import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testGroups() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/groups`,
        JSON.stringify({ name: `k6grp_crud${uid()}`, guard_name: 'api' }),
        JSON_HEADERS, 'groups'
    );
    check(res, { 'Groups CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/groups/${resourceId}`, null, AUTH_HEADERS, 'groups');
        check(res, { 'Groups READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/groups/${resourceId}`,
            JSON.stringify({ name: `k6grp_upd${uid()}`, guard_name: 'api' }),
            JSON_HEADERS, 'groups'
        );
        check(res, { 'Groups UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/groups/${resourceId}`, null, AUTH_HEADERS, 'groups');
        check(res, { 'Groups DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/groups`, null, AUTH_HEADERS, 'groups');
}