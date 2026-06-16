import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCommunityGroupAdminMembers(ids) {
    let res, resourceId;
    const memberData = {
        user_id: ids.userId,
        community_group_id: ids.communityGroupId,
    };
    const memberFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-group-admins/${ids.communityGroupAdminId}/members`,
        memberFd.body,
        { ...AUTH_HEADERS, ...memberFd.headers, Accept: 'application/json' },
        'community-group-admin-members'
    );
    check(res, { 'CommGroupAdminMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-admin-members');
        check(res, { 'CommGroupAdminMembers READ': (r) => r.status === 200 });

        const memberUpdFd = createMultipartForm(memberData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`,
            memberUpdFd.body,
            { ...AUTH_HEADERS, ...memberUpdFd.headers, Accept: 'application/json' },
            'community-group-admin-members'
        );
        check(res, { 'CommGroupAdminMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-admin-members');
        check(res, { 'CommGroupAdminMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-group-admins/${ids.communityGroupAdminId}/members`, null, AUTH_HEADERS, 'community-group-admin-members');
}