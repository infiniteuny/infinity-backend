import { check } from 'k6';
import { trackedRequest, extractId } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testCommunityGroupAdmins() {
    let res, resourceId;
    const year = 9000 + __VU;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-group-admins`,
        JSON.stringify({ year: year, is_active: false }),
        JSON_HEADERS, 'community-group-admins'
    );
    check(res, { 'CommGroupAdmins CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-group-admins/${resourceId}`, null, AUTH_HEADERS, 'community-group-admins');
        check(res, { 'CommGroupAdmins READ': (r) => r.status === 200 });

        res = trackedRequest(
            'PUT', `${BASE_URL}/v1/community-group-admins/${resourceId}`,
            JSON.stringify({ year: year }),
            JSON_HEADERS, 'community-group-admins'
        );
        check(res, { 'CommGroupAdmins UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-group-admins/${resourceId}`, null, AUTH_HEADERS, 'community-group-admins');
        check(res, { 'CommGroupAdmins DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-group-admins`, null, AUTH_HEADERS, 'community-group-admins');
}