import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testCommunityGroups() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/community-groups`,
        {
            name: `k6cg_crud${uid()}`,
            priority: '2',
            description: 'k6 crud test',
            is_active: '1',
            logo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'community-groups'
    );
    check(res, { 'CommGroups CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/community-groups/${resourceId}`, null, AUTH_HEADERS, 'community-groups');
        check(res, { 'CommGroups READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/community-groups/${resourceId}`,
            {
                _method: 'PUT',
                name: `k6cg_upd${uid()}`,
                priority: '2',
                description: 'k6 crud test updated',
                is_active: '1',
                logo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'community-groups'
        );
        check(res, { 'CommGroups UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/community-groups/${resourceId}`, null, AUTH_HEADERS, 'community-groups');
        check(res, { 'CommGroups DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/community-groups`, null, AUTH_HEADERS, 'community-groups');
}
