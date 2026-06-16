import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN, GIF_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCoreTeamMembers(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/core-teams/${ids.coreTeamId}/members`,
        {
            user_id: ids.memberUserId,
            core_team_division_id: ids.coreTeamDivisionId,
            photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            animation: http.file(GIF_BIN, 'test-animation.gif', 'image/gif'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'core-team-members'
    );
    check(res, { 'CoreTeamMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/core-team-members/${resourceId}`, null, AUTH_HEADERS, 'core-team-members');
        check(res, { 'CoreTeamMembers READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/core-team-members/${resourceId}`,
            {
                _method: 'PUT',
                user_id: ids.memberUserId,
                core_team_division_id: ids.coreTeamDivisionId,
                photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'core-team-members'
        );
        check(res, { 'CoreTeamMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/core-team-members/${resourceId}`, null, AUTH_HEADERS, 'core-team-members');
        check(res, { 'CoreTeamMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/core-teams/${ids.coreTeamId}/members`, null, AUTH_HEADERS, 'core-team-members');
}
