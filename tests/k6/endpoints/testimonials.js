import { check } from 'k6';
import { trackedRequest, extractId, uid, createMultipartForm } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testTestimonials() {
    let res, resourceId;
    const testData = { name: `k6test_crud${uid()}`, position: 'k6 tester', content: 'k6 test content' };
    const testFd = createMultipartForm(testData, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } });

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/testimonials`,
        testFd.body,
        { ...AUTH_HEADERS, ...testFd.headers, Accept: 'application/json' },
        'testimonials'
    );
    check(res, { 'Testimonials CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/testimonials/${resourceId}`, null, AUTH_HEADERS, 'testimonials');
        check(res, { 'Testimonials READ': (r) => r.status === 200 });

        const testUpdFd = createMultipartForm({ ...testData, name: `k6test_upd${uid()}` }, { photo: { filename: 'test-image.jpg', contentType: 'image/jpeg' } }, { methodOverride: 'PUT' });
        res = trackedRequest(
            'POST', `${BASE_URL}/v1/testimonials/${resourceId}`,
            testUpdFd.body,
            { ...AUTH_HEADERS, ...testUpdFd.headers, Accept: 'application/json' },
            'testimonials'
        );
        check(res, { 'Testimonials UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/testimonials/${resourceId}`, null, AUTH_HEADERS, 'testimonials');
        check(res, { 'Testimonials DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/testimonials`, null, AUTH_HEADERS, 'testimonials');
}