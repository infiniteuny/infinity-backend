import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitions() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competitions`,
        JSON.stringify({ name: `k6comp_crud${uid()}`, shortname: `COMP${uid()}`, description: 'k6' }),
        JSON_HEADERS, 'competitions'
    );
    check(res, { 'Competitions CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competitions/${resourceId}`, null, AUTH_HEADERS, 'competitions');
        check(res, { 'Competitions READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competitions/${resourceId}`,
            JSON.stringify({ name: `k6comp_upd${uid()}`, shortname: `CU${uid()}`, description: 'k6 updated' }),
            JSON_HEADERS, 'competitions'
        );
        check(res, { 'Competitions UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competitions/${resourceId}`, null, AUTH_HEADERS, 'competitions');
        check(res, { 'Competitions DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competitions`, null, AUTH_HEADERS, 'competitions');
}