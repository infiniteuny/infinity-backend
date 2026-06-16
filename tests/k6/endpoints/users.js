import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testUsers(ids) {
    let res, resourceId;
    const u = uid().replace(/_/g, '');

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/users`,
        JSON.stringify({
            name: `k6user_crud${uid()}`,
            username: `k6uc${u}`,
            email_address: `k6uc${u}@test.com`,
            phone_number: `089${u.slice(0, 10)}`,
            student_id: `99001${u.slice(0, 10)}`,
            major_id: ids.majorId,
            links: {},
            is_member: false,
            is_extraordinary: false,
        }),
        JSON_HEADERS, 'users'
    );
    check(res, { 'Users CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/users/${resourceId}`, null, AUTH_HEADERS, 'users');
        check(res, { 'Users READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/users/${resourceId}`,
            JSON.stringify({ name: `k6user_upd${uid()}`, links: {} }),
            JSON_HEADERS, 'users'
        );
        check(res, { 'Users UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/users/${resourceId}`, null, AUTH_HEADERS, 'users');
        check(res, { 'Users DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/users`, null, AUTH_HEADERS, 'users');
}
