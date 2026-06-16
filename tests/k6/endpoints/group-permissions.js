import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testGroupPermissions(ids) {
    let res, resourceId;
    const groupId = ids.groupId;
    const permissionId = ids.permissionId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/groups/${groupId}/permissions`,
        JSON.stringify({ permission_id: permissionId }),
        JSON_HEADERS, 'group-permissions'
    );
    check(res, { 'GroupPermissions CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/group-permissions/${resourceId}`, null, AUTH_HEADERS, 'group-permissions');
        check(res, { 'GroupPermissions READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/group-permissions/${resourceId}`,
            JSON.stringify({ permission_id: permissionId }),
            JSON_HEADERS, 'group-permissions'
        );
        check(res, { 'GroupPermissions UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/group-permissions/${resourceId}`, null, AUTH_HEADERS, 'group-permissions');
        check(res, { 'GroupPermissions DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/groups/${groupId}/permissions`, null, AUTH_HEADERS, 'group-permissions');
}