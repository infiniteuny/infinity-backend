import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionScales() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-scales`,
        JSON.stringify({ name: `k6cscale_crud${uid()}`, weight: 1 }),
        JSON_HEADERS, 'competition-scales'
    );
    check(res, { 'CompScales CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-scales/${resourceId}`, null, AUTH_HEADERS, 'competition-scales');
        check(res, { 'CompScales READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-scales/${resourceId}`,
            JSON.stringify({ name: `k6cscale_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-scales'
        );
        check(res, { 'CompScales UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-scales/${resourceId}`, null, AUTH_HEADERS, 'competition-scales');
        check(res, { 'CompScales DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-scales`, null, AUTH_HEADERS, 'competition-scales');
}