import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN, GIF_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCommunityGroupAdminMembers(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-group-admins/${ids.communityGroupAdminId}/members`,
        {
            user_id: ids.memberUserId,
            community_group_id: ids.communityGroupId,
            photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            animation: http.file(GIF_BIN, 'test-animation.gif', 'image/gif'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'community-group-admin-members'
    );
    check(res, { 'CommGroupAdminMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-admin-members');
        check(res, { 'CommGroupAdminMembers READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`,
            {
                _method: 'PUT',
                user_id: ids.memberUserId,
                community_group_id: ids.communityGroupId,
                photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'community-group-admin-members'
        );
        check(res, { 'CommGroupAdminMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-group-admin-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-admin-members');
        check(res, { 'CommGroupAdminMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-group-admins/${ids.communityGroupAdminId}/members`, null, AUTH_HEADERS, 'community-group-admin-members');
}
