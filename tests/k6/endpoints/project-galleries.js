import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testProjectGalleries() {
    let res, resourceId;
    const pgData = { title: `k6pg_crud${uid()}`, description: 'k6 crud', url: 'https://example.com' };
    const pgFd = createMultipartForm(pgData, { image: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/project-galleries`,
        pgFd.body,
        { ...AUTH_HEADERS, ...pgFd.headers, Accept: 'application/json' },
        'project-galleries'
    );
    check(res, { 'ProjGalleries CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/project-galleries/${resourceId}`, null, AUTH_HEADERS, 'project-galleries');
        check(res, { 'ProjGalleries READ': (r) => r.status === 200 });

        const pgUpdFd = createMultipartForm({ ...pgData, title: `k6pg_upd${uid()}` }, { image: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/project-galleries/${resourceId}`,
            pgUpdFd.body,
            { ...AUTH_HEADERS, ...pgUpdFd.headers, Accept: 'application/json' },
            'project-galleries'
        );
        check(res, { 'ProjGalleries UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/project-galleries/${resourceId}`, null, AUTH_HEADERS, 'project-galleries');
        check(res, { 'ProjGalleries DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/project-galleries`, null, AUTH_HEADERS, 'project-galleries');
}