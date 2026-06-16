import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCoreTeamMembers(ids) {
    let res, resourceId;
    const memberData = {
        user_id: ids.userId,
        core_team_division_id: ids.coreTeamDivisionId,
    };
    const memberFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/core-teams/${ids.coreTeamId}/members`,
        memberFd.body,
        { ...AUTH_HEADERS, ...memberFd.headers, Accept: 'application/json' },
        'core-team-members'
    );
    check(res, { 'CoreTeamMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/core-team-members/${resourceId}`, null, AUTH_HEADERS, 'core-team-members');
        check(res, { 'CoreTeamMembers READ': (r) => r.status === 200 });

        const memberUpdFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/core-team-members/${resourceId}`,
            memberUpdFd.body,
            { ...AUTH_HEADERS, ...memberUpdFd.headers, Accept: 'application/json' },
            'core-team-members'
        );
        check(res, { 'CoreTeamMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/core-team-members/${resourceId}`, null, AUTH_HEADERS, 'core-team-members');
        check(res, { 'CoreTeamMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/core-teams/${ids.coreTeamId}/members`, null, AUTH_HEADERS, 'core-team-members');
}