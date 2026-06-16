import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCompetitionTeamTypes() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/competition-team-types`,
        JSON.stringify({ name: `k6ctt_crud${uid()}`, shortname: `CT${uid()}`, description: 'k6' }),
        JSON_HEADERS, 'competition-team-types'
    );
    check(res, { 'CompTeamTypes CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/competition-team-types/${resourceId}`, null, AUTH_HEADERS, 'competition-team-types');
        check(res, { 'CompTeamTypes READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/competition-team-types/${resourceId}`,
            JSON.stringify({ name: `k6ctt_upd${uid()}`, weight: 2 }),
            JSON_HEADERS, 'competition-team-types'
        );
        check(res, { 'CompTeamTypes UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/competition-team-types/${resourceId}`, null, AUTH_HEADERS, 'competition-team-types');
        check(res, { 'CompTeamTypes DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/competition-team-types`, null, AUTH_HEADERS, 'competition-team-types');
}