import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testDegrees() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/degrees`,
        JSON.stringify({ name: `k6deg_crud${uid()}`, code: `K6DEG${uid()}` }),
        JSON_HEADERS, 'degrees'
    );
    check(res, { 'Degrees CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/degrees/${resourceId}`, null, AUTH_HEADERS, 'degrees');
        check(res, { 'Degrees READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/degrees/${resourceId}`,
            JSON.stringify({ name: `k6deg_upd${uid()}`, code: `K6DU${uid()}` }),
            JSON_HEADERS, 'degrees'
        );
        check(res, { 'Degrees UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/degrees/${resourceId}`, null, AUTH_HEADERS, 'degrees');
        check(res, { 'Degrees DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/degrees`, null, AUTH_HEADERS, 'degrees');
}