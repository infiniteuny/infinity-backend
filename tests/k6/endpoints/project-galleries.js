import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testProjectGalleries() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/project-galleries`,
        {
            title: `k6pg_crud${uid()}`,
            description: 'k6 crud',
            url: 'https://example.com',
            image: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'project-galleries'
    );
    check(res, { 'ProjGalleries CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/project-galleries/${resourceId}`, null, AUTH_HEADERS, 'project-galleries');
        check(res, { 'ProjGalleries READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/project-galleries/${resourceId}`,
            {
                _method: 'PUT',
                title: `k6pg_upd${uid()}`,
                description: 'k6 crud updated',
                url: 'https://example.com',
                image: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'project-galleries'
        );
        check(res, { 'ProjGalleries UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/project-galleries/${resourceId}`, null, AUTH_HEADERS, 'project-galleries');
        check(res, { 'ProjGalleries DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/project-galleries`, null, AUTH_HEADERS, 'project-galleries');
}
