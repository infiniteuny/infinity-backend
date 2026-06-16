import { check } from 'k6';
import { trackedRequest, extractId, uid } from '../lib/helpers.js';
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from '../lib/config.js';

export function testConfigs() {
    let res;
    const cfgKey = `k6cfg_crud${uid()}`;

    res = trackedRequest(
        'POST', `${BASE_URL}/v1/configs`,
        JSON.stringify({ key: cfgKey, value: 'k6test', type: 'STRING', is_private: false }),
        JSON_HEADERS, 'configs'
    );
    check(res, { 'Configs CREATE': (r) => r.status === 201 || r.status === 200 });

    res = trackedRequest('GET', `${BASE_URL}/v1/configs/${cfgKey}`, null, AUTH_HEADERS, 'configs');
    check(res, { 'Configs READ': (r) => r.status === 200 });

    res = trackedRequest(
        'PUT', `${BASE_URL}/v1/configs/${cfgKey}`,
        JSON.stringify({ key: cfgKey, value: 'k6test_updated', type: 'STRING', is_private: false }),
        JSON_HEADERS, 'configs'
    );
    check(res, { 'Configs UPDATE': (r) => r.status === 200 });

    res = trackedRequest('DELETE', `${BASE_URL}/v1/configs/${cfgKey}`, null, AUTH_HEADERS, 'configs');
    check(res, { 'Configs DELETE': (r) => r.status === 200 });

    trackedRequest('GET', `${BASE_URL}/v1/configs`, null, AUTH_HEADERS, 'configs');
}