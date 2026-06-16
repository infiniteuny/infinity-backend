import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionOutputs() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-outputs`,
        JSON.stringify({ name: `k6cout_crud${uid()}`, weight: 1 }),
        JSON_HEADERS, 'competition-outputs'
    );
    check(res, { 'CompOutputs CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-outputs/${resourceId}`, null, AUTH_HEADERS, 'competition-outputs');
        check(res, { 'CompOutputs READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-outputs/${resourceId}`,
            JSON.stringify({ name: `k6cout_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-outputs'
        );
        check(res, { 'CompOutputs UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-outputs/${resourceId}`, null, AUTH_HEADERS, 'competition-outputs');
        check(res, { 'CompOutputs DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-outputs`, null, AUTH_HEADERS, 'competition-outputs');
}