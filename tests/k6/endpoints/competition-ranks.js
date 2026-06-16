import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionRanks() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-ranks`,
        JSON.stringify({ name: `k6crank_crud${uid()}`, weight: 1 }),
        JSON_HEADERS, 'competition-ranks'
    );
    check(res, { 'CompRanks CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-ranks/${resourceId}`, null, AUTH_HEADERS, 'competition-ranks');
        check(res, { 'CompRanks READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-ranks/${resourceId}`,
            JSON.stringify({ name: `k6crank_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-ranks'
        );
        check(res, { 'CompRanks UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-ranks/${resourceId}`, null, AUTH_HEADERS, 'competition-ranks');
        check(res, { 'CompRanks DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-ranks`, null, AUTH_HEADERS, 'competition-ranks');
}