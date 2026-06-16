import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testUserGroups(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/users/${ids.userId}/groups`,
        JSON.stringify({ group_id: ids.groupId }),
        JSON_HEADERS, 'user-groups'
    );
    check(res, { 'UserGroups CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/user-groups/${resourceId}`,
            JSON.stringify({ group_id: ids.groupId }),
            JSON_HEADERS, 'user-groups'
        );
        check(res, { 'UserGroups UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/user-groups/${resourceId}`, null, AUTH_HEADERS, 'user-groups');
        check(res, { 'UserGroups DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/users/${ids.userId}/groups`, null, AUTH_HEADERS, 'user-groups');
}