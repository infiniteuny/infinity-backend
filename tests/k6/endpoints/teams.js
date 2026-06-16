import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testTeams(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/teams`,
        JSON.stringify({ leader_id: ids.userId, team_type_id: ids.competitionTeamTypeId, name: `k6team_crud${uid()}`, is_personal: false }),
        JSON_HEADERS, 'teams'
    );
    check(res, { 'Teams CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/teams/${resourceId}`, null, AUTH_HEADERS, 'teams');
        check(res, { 'Teams READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/teams/${resourceId}`,
            JSON.stringify({ leader_id: ids.userId, team_type_id: ids.competitionTeamTypeId, name: `k6team_upd${uid()}`, is_personal: false }),
            JSON_HEADERS, 'teams'
        );
        check(res, { 'Teams UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/teams/${resourceId}`, null, AUTH_HEADERS, 'teams');
        check(res, { 'Teams DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/teams`, null, AUTH_HEADERS, 'teams');
}