import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCommunityGroupMembers(ids) {
    let res, resourceId;
    const cgmData = { user_id: ids.userId };

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-groups/${ids.communityGroupId}/members`,
        JSON.stringify(cgmData),
        JSON_HEADERS, 'community-group-members'
    );
    check(res, { 'CommGroupMembers CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-group-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-members');
        check(res, { 'CommGroupMembers READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/community-group-members/${resourceId}`,
            JSON.stringify(cgmData),
            JSON_HEADERS, 'community-group-members'
        );
        check(res, { 'CommGroupMembers UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-group-members/${resourceId}`, null, AUTH_HEADERS, 'community-group-members');
        check(res, { 'CommGroupMembers DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-groups/${ids.communityGroupId}/members`, null, AUTH_HEADERS, 'community-group-members');
}