import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testUserPermissions(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/users/${ids.userId}/permissions`,
        JSON.stringify({ permission_id: ids.permissionId }),
        JSON_HEADERS, 'user-permissions'
    );
    check(res, { 'UserPermissions CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/user-permissions/${resourceId}`, null, AUTH_HEADERS, 'user-permissions');
        check(res, { 'UserPermissions READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/user-permissions/${resourceId}`,
            JSON.stringify({ permission_id: ids.permissionId }),
            JSON_HEADERS, 'user-permissions'
        );
        check(res, { 'UserPermissions UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/user-permissions/${resourceId}`, null, AUTH_HEADERS, 'user-permissions');
        check(res, { 'UserPermissions DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/users/${ids.userId}/permissions`, null, AUTH_HEADERS, 'user-permissions');
}