import http from 'k6/http';
import { Trend, Rate, Counter } from 'k6/metrics';
import { AUTH_HEADERS } from './config.js';

const apiResponseTime = new Trend('api_response_time', true);
const apiThroughput = new Counter('api_throughput');
const apiErrorRate = new Rate('api_error_rate');

function sanitizeMetricName(name) {
    return name.replace(/-/g, '_');
}

// Pre-declare all per-group metrics at init context (required by k6)
const GROUPS = [
    'setup', 'teardown',
    'degrees', 'faculties', 'majors',
    'competition-organizer-types', 'competition-outputs', 'competition-ranks',
    'competition-scales', 'competition-team-types', 'competition-time-ranges',
    'competitions', 'competition-instances',
    'community-group-admins', 'community-group-admin-members',
    'community-groups', 'community-group-members',
    'configs', 'core-team-divisions', 'core-teams', 'core-team-members',
    'groups', 'group-permissions', 'leaderboards', 'permissions', 'personas',
    'project-galleries', 'teams', 'team-members', 'testimonials', 'tokens',
    'user-community-groups', 'user-groups', 'user-permissions', 'user-personas',
    'users', 'achievements', 'fund-applications',
];

const groupMetrics = {};
for (const name of GROUPS) {
    const metricName = sanitizeMetricName(name);
    groupMetrics[name] = {
        responseTime: new Trend(`${metricName}_response_time`, true),
        errorRate: new Rate(`${metricName}_error_rate`),
        throughput: new Counter(`${metricName}_throughput`),
    };
}

export function trackedRequest(method, url, body, headers, groupName) {
    const params = { headers: headers || AUTH_HEADERS, tags: { group: groupName } };

    let res;
    switch (method.toUpperCase()) {
        case 'GET':
            res = http.get(url, params);
            break;
        case 'POST':
            res = http.post(url, body, params);
            break;
        case 'PUT':
            res = http.put(url, body, params);
            break;
        case 'DELETE':
            res = http.del(url, null, params);
            break;
        default:
            res = http.request(method, url, body, params);
    }

    const isError = res.status >= 400;

    apiResponseTime.add(res.timings.duration);
    apiThroughput.add(1);
    apiErrorRate.add(isError ? 1 : 0);

    const gm = groupMetrics[groupName];
    if (gm) {
        gm.responseTime.add(res.timings.duration);
        gm.throughput.add(1);
        gm.errorRate.add(isError ? 1 : 0);
    }

    return res;
}

export function extractId(res) {
    try {
        const body = res.json();
        const data = body.data;
        if (!data) return null;
        const keys = Object.keys(data);
        for (const key of keys) {
            if (data[key] && data[key].id) return data[key].id;
        }
    } catch (_) { }
    return null;
}

let _uidCounter = 0;
export function uid() {
    _uidCounter++;
    return `_k6_${_uidCounter}_${Date.now() % 100000}`;
}

export function createMultipartForm(fields, files, options = {}) {
    const boundary = `----k6Boundary${Date.now()}${Math.random().toString(36).slice(2)}`;
    const parts = [];

    const allFields = { ...fields };
    if (options.methodOverride) {
        allFields._method = options.methodOverride;
    }

    for (const [key, value] of Object.entries(allFields)) {
        if (value !== undefined && value !== null) {
            const strValue = typeof value === 'boolean' ? (value ? '1' : '0') : String(value);
            parts.push(
                `--${boundary}\r\nContent-Disposition: form-data; name="${key}"\r\n\r\n${strValue}`
            );
        }
    }

    if (files) {
        for (const [key, file] of Object.entries(files)) {
            const fileData = (() => {
                try {
                    return open(`tests/k6/fixtures/${file.filename}`, 'b');
                } catch (_) {
                    return '';
                }
            })();
            parts.push(
                `--${boundary}\r\nContent-Disposition: form-data; name="${key}"; filename="${file.filename}"\r\nContent-Type: ${file.contentType}\r\n\r\n${fileData}`
            );
        }
    }

    parts.push(`--${boundary}--\r\n`);

    const body = parts.join('\r\n');

    return {
        body,
        headers: { 'Content-Type': `multipart/form-data; boundary=${boundary}` },
    };
}