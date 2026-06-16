import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testUsers(ids) {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/users`,
        JSON.stringify({ name: `k6user_crud${uid()}`, username: `k6uc${uid()}`, email_address: `k6uc${uid()}@test.com`, phone_number: '08123456789', student_id: `99001${uid().replace(/_/g, '')}`, major_id: ids.majorId, links: {}, is_member: true, is_extraordinary: false }),
        JSON_HEADERS, 'users'
    );
    check(res, { 'Users CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/users/${resourceId}`, null, AUTH_HEADERS, 'users');
        check(res, { 'Users READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/users/${resourceId}`,
            JSON.stringify({ name: `k6user_upd${uid()}`, username: `k6uu${uid()}`, links: {}, is_member: true }),
            JSON_HEADERS, 'users'
        );
        check(res, { 'Users UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/users/${resourceId}`, null, AUTH_HEADERS, 'users');
        check(res, { 'Users DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/users`, null, AUTH_HEADERS, 'users');
}