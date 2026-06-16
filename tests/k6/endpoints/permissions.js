import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testPermissions() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/permissions`,
        JSON.stringify({ name: `k6perm_crud${uid()}`, guard_name: 'api' }),
        JSON_HEADERS, 'permissions'
    );
    check(res, { 'Permissions CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/permissions/${resourceId}`, null, AUTH_HEADERS, 'permissions');
        check(res, { 'Permissions READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/permissions/${resourceId}`,
            JSON.stringify({ name: `k6perm_upd${uid()}`, guard_name: 'api' }),
            JSON_HEADERS, 'permissions'
        );
        check(res, { 'Permissions UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/permissions/${resourceId}`, null, AUTH_HEADERS, 'permissions');
        check(res, { 'Permissions DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/permissions`, null, AUTH_HEADERS, 'permissions');
}