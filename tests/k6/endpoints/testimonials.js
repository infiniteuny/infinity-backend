import http from 'k6/http';
import { check } from 'k6';
import { trackedRequest, extractId, uid, IMAGE_BIN } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS } from '../lib/config.js';

export function testTestimonials() {
    let res, resourceId;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/testimonials`,
        {
            name: `k6test_crud${uid()}`,
            position: 'k6 tester',
            content: 'k6 test content',
            photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
        },
        { ...AUTH_HEADERS, Accept: 'application/json' },
        'testimonials'
    );
    check(res, { 'Testimonials CREATE': (r) => r.status === 201 || r.status === 200 });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest('GET', `${BASE_URL}/v1/testimonials/${resourceId}`, null, AUTH_HEADERS, 'testimonials');
        check(res, { 'Testimonials READ': (r) => r.status === 200 });

        res = trackedRequest(
            'POST', `${BASE_URL}/v1/testimonials/${resourceId}`,
            {
                _method: 'PUT',
                name: `k6test_upd${uid()}`,
                position: 'k6 tester updated',
                content: 'k6 test content updated',
                photo: http.file(IMAGE_BIN, 'test-image.jpg', 'image/jpeg'),
            },
            { ...AUTH_HEADERS, Accept: 'application/json' },
            'testimonials'
        );
        check(res, { 'Testimonials UPDATE': (r) => r.status === 200 });

        res = trackedRequest('DELETE', `${BASE_URL}/v1/testimonials/${resourceId}`, null, AUTH_HEADERS, 'testimonials');
        check(res, { 'Testimonials DELETE': (r) => r.status === 200 });
    }

    trackedRequest('GET', `${BASE_URL}/v1/testimonials`, null, AUTH_HEADERS, 'testimonials');
}
