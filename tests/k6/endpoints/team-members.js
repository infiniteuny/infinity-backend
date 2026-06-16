import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testTeamMembers(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/teams/${ids.teamId}/members`,
        JSON.stringify({ user_id: ids.memberUserId }),
        JSON_HEADERS,
        'team-members'
    );
    check(res, { 'TeamMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/team-members/${resourceId}`, null, AUTH_HEADERS, 'team-members');
        check(res, { 'TeamMembers READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/team-members/${resourceId}`,
            JSON.stringify({ user_id: ids.memberUserId }),
            JSON_HEADERS,
            'team-members'
        );
        check(res, { 'TeamMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/team-members/${resourceId}`, null, AUTH_HEADERS, 'team-members');
        check(res, { 'TeamMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/teams/${ids.teamId}/members`, null, AUTH_HEADERS, 'team-members');
}
