import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testTeamMembers(ids) {
    let resourceTeamId = ids.teamId;
    let res, resourceId;
    const memberData = {
        user_id: ids.userId,
    };
    const memberFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/teams/${resourceTeamId}/members`,
        memberFd.body,
        { ...AUTH_HEADERS, ...memberFd.headers, Accept: 'application/json' },
        'team-members'
    );
    check(res, { 'TeamMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/team-members/${resourceId}`, null, AUTH_HEADERS, 'team-members');
        check(res, { 'TeamMembers READ': (r) => r.status === 200 });

        const memberUpdFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/team-members/${resourceId}`,
            memberUpdFd.body,
            { ...AUTH_HEADERS, ...memberUpdFd.headers, Accept: 'application/json' },
            'team-members'
        );
        check(res, { 'TeamMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/team-members/${resourceId}`, null, AUTH_HEADERS, 'team-members');
        check(res, { 'TeamMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/teams/${resourceTeamId}/members`, null, AUTH_HEADERS, 'team-members');
}