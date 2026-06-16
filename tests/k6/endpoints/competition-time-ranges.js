import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionTimeRanges() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-time-ranges`,
        JSON.stringify({ name: `k6ctr_crud${uid()}`, weight: 1 }),
        JSON_HEADERS, 'competition-time-ranges'
    );
    check(res, { 'CompTimeRanges CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-time-ranges/${resourceId}`, null, AUTH_HEADERS, 'competition-time-ranges');
        check(res, { 'CompTimeRanges READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-time-ranges/${resourceId}`,
            JSON.stringify({ name: `k6ctr_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-time-ranges'
        );
        check(res, { 'CompTimeRanges UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-time-ranges/${resourceId}`, null, AUTH_HEADERS, 'competition-time-ranges');
        check(res, { 'CompTimeRanges DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-time-ranges`, null, AUTH_HEADERS, 'competition-time-ranges');
}