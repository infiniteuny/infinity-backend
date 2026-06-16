import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCommunityGroups() {
    let res, resourceId;
    const cgData = { name: `k6cg_crud${uid()}`, priority: 2, description: 'k6 crud test', is_active: '1' };
    const cgFd = createMultipartForm(cgData, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-groups`,
        cgFd.body,
        { ...AUTH_HEADERS, ...cgFd.headers, Accept: 'application/json' },
        'community-groups'
    );
    check(res, { 'CommGroups CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-groups/${resourceId}`, null, AUTH_HEADERS, 'community-groups');
        check(res, { 'CommGroups READ': (r) => r.status === 200 });

        const cgUpdFd = createMultipartForm({ ...cgData, name: `k6cg_upd${uid()}` }, { logo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-groups/${resourceId}`,
            cgUpdFd.body,
            { ...AUTH_HEADERS, ...cgUpdFd.headers, Accept: 'application/json' },
            'community-groups'
        );
        check(res, { 'CommGroups UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-groups/${resourceId}`, null, AUTH_HEADERS, 'community-groups');
        check(res, { 'CommGroups DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-groups`, null, AUTH_HEADERS, 'community-groups');
}