import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testFaculties() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/faculties`,
        JSON.stringify({ name: `k6fac_crud${uid()}`, code: `K6FC${uid()}`, shortname: `FC${uid()}`, description: 'k6' }),
        JSON_HEADERS, 'faculties'
    );
    check(res, { 'Faculties CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/faculties/${resourceId}`, null, AUTH_HEADERS, 'faculties');
        check(res, { 'Faculties READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/faculties/${resourceId}`,
            JSON.stringify({ name: `k6fac_upd${uid()}`, code: `K6FU${uid()}`, shortname: `FU${uid()}`, description: 'k6 updated' }),
            JSON_HEADERS, 'faculties'
        );
        check(res, { 'Faculties UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/faculties/${resourceId}`, null, AUTH_HEADERS, 'faculties');
        check(res, { 'Faculties DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/faculties`, null, AUTH_HEADERS, 'faculties');
}