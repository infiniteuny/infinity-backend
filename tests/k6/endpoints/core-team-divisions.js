import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCoreTeamDivisions() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/core-team-divisions`,
        JSON.stringify({ name: `k6div_crud${uid()}`, priority: 1 }),
        JSON_HEADERS, 'core-team-divisions'
    );
    check(res, { 'CoreTeamDivs CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/core-team-divisions/${resourceId}`, null, AUTH_HEADERS, 'core-team-divisions');
        check(res, { 'CoreTeamDivs READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/core-team-divisions/${resourceId}`,
JSON.stringify({ name: `k6div_upd${uid()}`, priority: 2 }),
        JSON_HEADERS, 'core-team-divisions'
        );
        check(res, { 'CoreTeamDivs UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/core-team-divisions/${resourceId}`, null, AUTH_HEADERS, 'core-team-divisions');
        check(res, { 'CoreTeamDivs DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/core-team-divisions`, null, AUTH_HEADERS, 'core-team-divisions');
}