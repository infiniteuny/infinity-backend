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

// ---- File fixtures opened at init context (k6 requirement) ----
export const IMAGE_BIN = open('../fixtures/test-image.jpg', 'b');
export const GIF_BIN = open('../fixtures/test-animation.gif', 'b');
export const PDF_BIN = open('../fixtures/test-document.pdf', 'b');
export const DOCX_BIN = open('../fixtures/test-document.docx', 'b');

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

/**
 * Extract ID from API response.
 * Handles both normal resources and pivot resources.
 * Normal: { data: { degree: { id: "..." } } }
 * Pivot:  { data: { user: { id: "...", membership: { id: "..." } } } }
 *         { data: { permission: { id: "...", entitlement: { id: "..." } } } }
 *         { data: { group: { id: "...", entitlement: { id: "..." } } } }
 */
export function extractId(res) {
    try {
        const body = res.json();
        const data = body.data;
        if (!data) return null;
        const keys = Object.keys(data);
        for (const key of keys) {
            const resource = data[key];
            if (!resource || !resource.id) continue;
            // For pivot resources, return the pivot ID (membership.id or entitlement.id)
            if (resource.membership && resource.membership.id) return resource.membership.id;
            if (resource.entitlement && resource.entitlement.id) return resource.entitlement.id;
            return resource.id;
        }
    } catch (_) { }
    return null;
}

let _uidCounter = 0;
export function uid() {
    _uidCounter++;
    return `_k6_${_uidCounter}_${Date.now() % 100000}`;
}
