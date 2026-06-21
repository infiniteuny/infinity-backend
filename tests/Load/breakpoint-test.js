import { group, sleep } from "k6";
import { BASE_URL, AUTH_HEADERS, JSON_HEADERS } from "./lib/config.js";
import { trackedRequest, extractId, uid } from "./lib/helpers.js";

// Seeder modules
import { generateDegreeData } from "./lib/seeders/degrees.js";
import { generateFacultyData } from "./lib/seeders/faculties.js";
import { generateMajorData } from "./lib/seeders/majors.js";
import { generateCompetitionOrganizerTypeData } from "./lib/seeders/competition-organizer-types.js";
import { generateCompetitionOutputData } from "./lib/seeders/competition-outputs.js";
import { generateCompetitionRankData } from "./lib/seeders/competition-ranks.js";
import { generateCompetitionScaleData } from "./lib/seeders/competition-scales.js";
import { generateCompetitionTeamTypeData } from "./lib/seeders/competition-team-types.js";
import { generateCompetitionTimeRangeData } from "./lib/seeders/competition-time-ranges.js";
import { generateCompetitionData } from "./lib/seeders/competitions.js";
import { generateCompetitionInstanceData } from "./lib/seeders/competition-instances.js";
import { generateUserData } from "./lib/seeders/users.js";
import { generateCommunityGroupData } from "./lib/seeders/community-groups.js";
import { generateConfigData } from "./lib/seeders/configs.js";
import { generateTeamData } from "./lib/seeders/teams.js";

// Endpoint modules - only high-impact endpoints per LOAD_TESTING.md
import { testUsers } from "./lib/endpoints/users.js";
import { testUserPermissions } from "./lib/endpoints/user-permissions.js";
import { testCompetitions } from "./lib/endpoints/competitions.js";
import { testConfigs } from "./lib/endpoints/configs.js";
import { testCommunityGroups } from "./lib/endpoints/community-groups.js";
import { testTestimonials } from "./lib/endpoints/testimonials.js";
import {
    testFundApplications,
    testFundApplicationsUpdate,
} from "./lib/endpoints/fund-applications.js";
import {
    testAchievements,
    testAchievementsUpdate,
} from "./lib/endpoints/achievements.js";
import { testTeams } from "./lib/endpoints/teams.js";

// ============================================================================
// Breakpoint Test Options - Multi-Scenario with Smooth VU Ramp-up
// Combines traffic mix (80/15/5) with breakpoint-style gradual ramp-up
// Uses ramping-vus executor for direct VU control
// Max VUs capped at 200 total
// ============================================================================
export const options = {
    scenarios: {
        // Volume Grinders (Reads) - 80% of traffic (160 VUs max)
        reads_users: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 12 },
                { duration: "10m", target: 25 },
                { duration: "10m", target: 37 },
                { duration: "10m", target: 50 },
            ],
            gracefulRampDown: "30s",
            exec: "readUsers",
        },
        reads_user_permissions: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 10 },
                { duration: "10m", target: 20 },
                { duration: "10m", target: 30 },
                { duration: "10m", target: 40 },
            ],
            gracefulRampDown: "30s",
            exec: "readUserPermissions",
        },
        reads_competitions: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 7 },
                { duration: "10m", target: 15 },
                { duration: "10m", target: 22 },
                { duration: "10m", target: 30 },
            ],
            gracefulRampDown: "30s",
            exec: "readCompetitions",
        },
        reads_configs: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 5 },
                { duration: "10m", target: 10 },
                { duration: "10m", target: 15 },
                { duration: "10m", target: 20 },
            ],
            gracefulRampDown: "30s",
            exec: "readConfigs",
        },
        reads_community_groups: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 2 },
                { duration: "10m", target: 5 },
                { duration: "10m", target: 7 },
                { duration: "10m", target: 10 },
            ],
            gracefulRampDown: "30s",
            exec: "readCommunityGroups",
        },
        reads_testimonials: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 2 },
                { duration: "10m", target: 5 },
                { duration: "10m", target: 7 },
                { duration: "10m", target: 10 },
            ],
            gracefulRampDown: "30s",
            exec: "readTestimonials",
        },

        // Financial Bottlenecks (Creates) - 15% of traffic (30 VUs max)
        creates_fund_applications: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 3 },
                { duration: "10m", target: 6 },
                { duration: "10m", target: 9 },
                { duration: "10m", target: 12 },
            ],
            gracefulRampDown: "30s",
            exec: "createFundApplications",
        },
        creates_achievements: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 2 },
                { duration: "10m", target: 5 },
                { duration: "10m", target: 7 },
                { duration: "10m", target: 10 },
            ],
            gracefulRampDown: "30s",
            exec: "createAchievements",
        },
        creates_teams: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 2 },
                { duration: "10m", target: 4 },
                { duration: "10m", target: 6 },
                { duration: "10m", target: 8 },
            ],
            gracefulRampDown: "30s",
            exec: "createTeams",
        },

        // Concurrency Traps (Updates) - 5% of traffic (10 VUs max)
        updates_fund_applications: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 1 },
                { duration: "10m", target: 3 },
                { duration: "10m", target: 4 },
                { duration: "10m", target: 6 },
            ],
            gracefulRampDown: "30s",
            exec: "updateFundApplications",
        },
        updates_achievements: {
            executor: "ramping-vus",
            startVUs: 0,
            stages: [
                { duration: "10m", target: 1 },
                { duration: "10m", target: 2 },
                { duration: "10m", target: 3 },
                { duration: "10m", target: 4 },
            ],
            gracefulRampDown: "30s",
            exec: "updateAchievements",
        },
    },
    thresholds: {
        // Response Time (Phase 2.1) - abort if exceeded
        http_req_duration: [
            { threshold: "p(50)<1000", abortOnFail: false },
            { threshold: "p(95)<3000", abortOnFail: false },
            { threshold: "p(99)<10000", abortOnFail: true },
        ],
        // Error Rate (Phase 2.3) - abort if exceeded
        http_req_failed: [{ threshold: "rate<0.01", abortOnFail: true }],
        api_error_rate: [{ threshold: "rate<0.01", abortOnFail: true }],
        // Throughput (Phase 2.2) - tracked but not aborted
        api_throughput: ["count>0"],
    },
    summaryTrendStats: [
        "avg",
        "min",
        "med",
        "max",
        "p(50)",
        "p(90)",
        "p(95)",
        "p(99)",
    ],
};

// ============================================================================
// Setup: Create prerequisite resources
// ============================================================================
export function setup() {
    const ids = {};
    let hasFailure = false;

    group("Setup - Prerequisite Resources", () => {
        // Helper function to create resource and extract ID
        const createResource = (method, url, data, headers, resourceName) => {
            const res = trackedRequest(method, url, data, headers, "setup");
            const id = extractId(res);
            if (!id) {
                console.error(
                    `Failed to create ${resourceName}: ${res.status} - ${res.body}`,
                );
                hasFailure = true;
                return null;
            }
            return id;
        };

        // Degrees
        ids.degreeId = createResource(
            "POST",
            `${BASE_URL}/v1/degrees`,
            JSON.stringify(generateDegreeData()),
            JSON_HEADERS,
            "Degree",
        );

        // Faculties
        ids.facultyId = createResource(
            "POST",
            `${BASE_URL}/v1/faculties`,
            JSON.stringify(generateFacultyData()),
            JSON_HEADERS,
            "Faculty",
        );

        // Majors (requires degreeId and facultyId)
        if (ids.degreeId && ids.facultyId) {
            ids.majorId = createResource(
                "POST",
                `${BASE_URL}/v1/majors`,
                JSON.stringify(generateMajorData(ids)),
                JSON_HEADERS,
                "Major",
            );
        } else {
            console.error(
                "Skipping Major creation: missing degreeId or facultyId",
            );
            hasFailure = true;
        }

        // Competition Organizer Types
        ids.competitionOrganizerTypeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-organizer-types`,
            JSON.stringify(generateCompetitionOrganizerTypeData()),
            JSON_HEADERS,
            "Competition Organizer Type",
        );

        // Competition Outputs
        ids.competitionOutputId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-outputs`,
            JSON.stringify(generateCompetitionOutputData()),
            JSON_HEADERS,
            "Competition Output",
        );

        // Competition Ranks
        ids.competitionRankId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-ranks`,
            JSON.stringify(generateCompetitionRankData()),
            JSON_HEADERS,
            "Competition Rank",
        );

        // Competition Scales
        ids.competitionScaleId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-scales`,
            JSON.stringify(generateCompetitionScaleData()),
            JSON_HEADERS,
            "Competition Scale",
        );

        // Competition Team Types
        ids.competitionTeamTypeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-team-types`,
            JSON.stringify(generateCompetitionTeamTypeData()),
            JSON_HEADERS,
            "Competition Team Type",
        );

        // Competition Time Ranges
        ids.competitionTimeRangeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-time-ranges`,
            JSON.stringify(generateCompetitionTimeRangeData()),
            JSON_HEADERS,
            "Competition Time Range",
        );

        // Competitions
        ids.competitionId = createResource(
            "POST",
            `${BASE_URL}/v1/competitions`,
            JSON.stringify(generateCompetitionData()),
            JSON_HEADERS,
            "Competition",
        );

        // Competition Instances (requires competitionId and competitionOrganizerTypeId)
        if (ids.competitionId && ids.competitionOrganizerTypeId) {
            ids.competitionInstanceId = createResource(
                "POST",
                `${BASE_URL}/v1/competition-instances`,
                generateCompetitionInstanceData(ids),
                { ...AUTH_HEADERS, Accept: "application/json" },
                "Competition Instance",
            );
        } else {
            console.error(
                "Skipping Competition Instance creation: missing competitionId or competitionOrganizerTypeId",
            );
            hasFailure = true;
        }

        // Users - leader user (requires majorId)
        if (ids.majorId) {
            const leaderData = generateUserData(ids);
            const leaderUid = uid().replace(/_/g, "");
            leaderData.username = `k6sl${leaderUid}`;
            leaderData.email_address = `k6sl${leaderUid}@test.com`;
            leaderData.phone_number = `081${leaderUid.slice(0, 10)}`;
            leaderData.student_id = `11111${leaderUid.slice(0, 10)}`;
            ids.userId = createResource(
                "POST",
                `${BASE_URL}/v1/users`,
                JSON.stringify(leaderData),
                JSON_HEADERS,
                "Leader User",
            );
        } else {
            console.error("Skipping Leader User creation: missing majorId");
            hasFailure = true;
        }

        // Users - member user (requires majorId)
        if (ids.majorId) {
            const memberData = generateUserData(ids);
            const memberUid = uid().replace(/_/g, "");
            memberData.username = `k6sm${memberUid}`;
            memberData.email_address = `k6sm${memberUid}@test.com`;
            memberData.phone_number = `082${memberUid.slice(0, 10)}`;
            memberData.student_id = `22222${memberUid.slice(0, 10)}`;
            ids.memberUserId = createResource(
                "POST",
                `${BASE_URL}/v1/users`,
                JSON.stringify(memberData),
                JSON_HEADERS,
                "Member User",
            );
        } else {
            console.error("Skipping Member User creation: missing majorId");
            hasFailure = true;
        }

        // Community Groups (multipart)
        ids.communityGroupId = createResource(
            "POST",
            `${BASE_URL}/v1/community-groups`,
            generateCommunityGroupData(),
            { ...AUTH_HEADERS, Accept: "application/json" },
            "Community Group",
        );

        // Configs
        const configData = generateConfigData();
        ids.configKey = configData.key;
        const configRes = trackedRequest(
            "POST",
            `${BASE_URL}/v1/configs`,
            JSON.stringify(configData),
            JSON_HEADERS,
            "setup",
        );
        if (configRes.status !== 201 && configRes.status !== 200) {
            console.error(
                `Failed to create Config: ${configRes.status} - ${configRes.body}`,
            );
            ids.configKey = null;
            hasFailure = true;
        }

        // Teams (requires userId and competitionTeamTypeId)
        if (ids.userId && ids.competitionTeamTypeId) {
            ids.teamId = createResource(
                "POST",
                `${BASE_URL}/v1/teams`,
                JSON.stringify(generateTeamData(ids)),
                JSON_HEADERS,
                "Team",
            );
        } else {
            console.error(
                "Skipping Team creation: missing userId or competitionTeamTypeId",
            );
            hasFailure = true;
        }

        // Permissions (required for user-permissions test)
        ids.permissionId = createResource(
            "POST",
            `${BASE_URL}/v1/permissions`,
            JSON.stringify({ name: `k6_perm_${uid()}`, guard_name: "api" }),
            JSON_HEADERS,
            "Permission",
        );

        // User-Permissions (nested resource)
        if (ids.userId && ids.permissionId) {
            ids.userPermissionId = createResource(
                "POST",
                `${BASE_URL}/v1/users/${ids.userId}/permissions`,
                JSON.stringify({ permission_id: ids.permissionId }),
                JSON_HEADERS,
                "User-Permission",
            );
        }
    });

    return { ids, hasFailure };
}

// ============================================================================
// Teardown: Cleanup
// ============================================================================
export function teardown(setupResult) {
    if (!setupResult || !setupResult.ids) return;
    const ids = setupResult.ids;

    group("Teardown - Cleanup", () => {
        const deletions = [
            // Level 4: Delete resources that depend on multiple other resources
            ids.userPermissionId
                ? `/v1/user-permissions/${ids.userPermissionId}`
                : null,
            ids.teamId ? `/v1/teams/${ids.teamId}` : null,
            ids.competitionInstanceId
                ? `/v1/competition-instances/${ids.competitionInstanceId}`
                : null,

            // Level 3: Delete resources that depend on majors
            ids.memberUserId ? `/v1/users/${ids.memberUserId}` : null,
            ids.userId ? `/v1/users/${ids.userId}` : null,

            // Level 2: Delete resources that depend on degrees/faculties
            ids.majorId ? `/v1/majors/${ids.majorId}` : null,

            // Level 1: Delete base resources (no dependencies)
            ids.permissionId ? `/v1/permissions/${ids.permissionId}` : null,
            ids.competitionId ? `/v1/competitions/${ids.competitionId}` : null,
            ids.communityGroupId
                ? `/v1/community-groups/${ids.communityGroupId}`
                : null,
            ids.configKey ? `/v1/configs/${ids.configKey}` : null,
            ids.competitionTeamTypeId
                ? `/v1/competition-team-types/${ids.competitionTeamTypeId}`
                : null,
            ids.competitionTimeRangeId
                ? `/v1/competition-time-ranges/${ids.competitionTimeRangeId}`
                : null,
            ids.competitionScaleId
                ? `/v1/competition-scales/${ids.competitionScaleId}`
                : null,
            ids.competitionRankId
                ? `/v1/competition-ranks/${ids.competitionRankId}`
                : null,
            ids.competitionOutputId
                ? `/v1/competition-outputs/${ids.competitionOutputId}`
                : null,
            ids.competitionOrganizerTypeId
                ? `/v1/competition-organizer-types/${ids.competitionOrganizerTypeId}`
                : null,
            ids.degreeId ? `/v1/degrees/${ids.degreeId}` : null,
            ids.facultyId ? `/v1/faculties/${ids.facultyId}` : null,
        ];

        for (const path of deletions) {
            if (path) {
                trackedRequest(
                    "DELETE",
                    `${BASE_URL}${path}`,
                    null,
                    AUTH_HEADERS,
                    "teardown",
                );
            }
        }
    });
}

// ============================================================================
// Scenario Executors - One function per scenario
// Per LOAD_TESTING.md Appendix: Endpoint Classification Matrix
// ============================================================================

// Volume Grinders (Reads) - 80% of traffic
export function readUsers(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Users READ", () => testUsers(setupResult.ids));
    sleep(0.5);
}

export function readUserPermissions(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("User Permissions READ", () => testUserPermissions(setupResult.ids));
    sleep(0.5);
}

export function readCompetitions(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Competitions READ", () => testCompetitions());
    sleep(0.5);
}

export function readConfigs(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Configs READ", () => testConfigs());
    sleep(0.5);
}

export function readCommunityGroups(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Community Groups READ", () => testCommunityGroups());
    sleep(0.5);
}

export function readTestimonials(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Testimonials READ", () => testTestimonials());
    sleep(0.5);
}

// Financial Bottlenecks (Creates) - 15% of traffic
export function createFundApplications(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Fund Applications CREATE", () =>
        testFundApplications(setupResult.ids),
    );
    sleep(0.5);
}

export function createAchievements(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Achievements CREATE", () => testAchievements(setupResult.ids));
    sleep(0.5);
}

export function createTeams(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Teams CREATE", () => testTeams(setupResult.ids));
    sleep(0.5);
}

// Concurrency Traps (Updates) - 5% of traffic
export function updateFundApplications(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Fund Applications UPDATE", () =>
        testFundApplicationsUpdate(setupResult.ids),
    );
    sleep(0.5);
}

export function updateAchievements(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Achievements UPDATE", () => testAchievementsUpdate(setupResult.ids));
    sleep(0.5);
}

// ============================================================================
// handleSummary: Write JSON results to file, standard k6 summary to console
// ============================================================================
export function handleSummary(data) {
    const logsDir = __ENV.K6_LOGS_DIR || "tests/k6/logs";
    const timestamp = new Date().toISOString().replace(/[:.]/g, "-");

    const metrics = data.metrics;
    const httpReqDuration = metrics.http_req_duration?.values;
    const httpReqFailed = metrics.http_req_failed?.values;
    const apiErrorRate = metrics.api_error_rate?.values;
    const apiThroughput = metrics.api_throughput?.values;
    const iterations = metrics.iterations?.values;
    const vus = metrics.vus?.values;
    const vusMax = metrics.vus_max?.values;

    const fmt = (v) => (v != null ? v.toFixed(2) : "N/A");
    const pct = (val, key) => {
        if (!val) return "N/A";
        const m = val[key];
        return m != null ? (m * 100).toFixed(2) + "%" : "N/A";
    };

    // Calculate test duration in seconds
    const durationSec = data.state?.testRunDurationMs
        ? data.state.testRunDurationMs / 1000
        : 0;

    // Calculate throughput (requests per second)
    const totalRequests = apiThroughput?.count || 0;
    const rps = durationSec > 0 ? totalRequests / durationSec : 0;

    const lines = [
        "\n=== k6 Breakpoint Test Summary ===\n",
        `Duration:        ${fmt(durationSec)}s`,
        `Iterations:      ${iterations?.count ?? "N/A"}`,
        "",
        "--- Phase 2.1: Response Time (ms) ---",
        `  P50:   ${fmt(httpReqDuration?.["p(50)"])}`,
        `  P95:   ${fmt(httpReqDuration?.["p(95)"])}`,
        `  P99:   ${fmt(httpReqDuration?.["p(99)"])}`,
        `  avg:   ${fmt(httpReqDuration?.avg)}`,
        `  min:   ${fmt(httpReqDuration?.min)}`,
        `  max:   ${fmt(httpReqDuration?.max)}`,
        "",
        "--- Phase 2.2: Throughput ---",
        `  Total requests:    ${totalRequests}`,
        `  Requests/second:   ${fmt(rps)}`,
        "",
        "--- Phase 2.3: Error Rate ---",
        `  HTTP failed rate:  ${pct(httpReqFailed, "rate")}`,
        `  API error rate:    ${pct(apiErrorRate, "rate")}`,
        "",
        "--- Phase 2.4: Virtual Users ---",
        `  Current VUs:       ${vus?.max ?? "N/A"}`,
        `  Max VUs:           ${vusMax?.max ?? "N/A"}`,
        "",
        "--- Per-Scenario Breakdown ---",
    ];

    // Extract scenario names from metrics (scenarios create groups like ::scenario_name::)
    const scenarioNames = Object.keys(data.metrics)
        .filter((k) => k.endsWith("_response_time"))
        .map((k) => k.replace("_response_time", ""))
        .filter((name) => !["setup", "teardown", "api"].includes(name));

    for (const scenarioName of scenarioNames) {
        const rt = metrics[`${scenarioName}_response_time`]?.values;
        const er = metrics[`${scenarioName}_error_rate`]?.values;
        const tp = metrics[`${scenarioName}_throughput`]?.values;
        if (!rt) continue;
        const scenarioRps =
            durationSec > 0 && tp?.count ? tp.count / durationSec : 0;
        lines.push(`  ${scenarioName}:`);
        lines.push(
            `    P50=${fmt(rt["p(50)"])}ms  P95=${fmt(rt["p(95)"])}ms  P99=${fmt(rt["p(99)"])}ms`,
        );
        lines.push(
            `    errors=${pct(er, "rate")}  reqs=${tp?.count ?? 0}  rps=${fmt(scenarioRps)}`,
        );
    }
    lines.push("\n=== End Summary ===\n");

    return {
        stdout: lines.join("\n"),
        [`${logsDir}/results-${timestamp}.json`]: JSON.stringify(data, null, 2),
    };
}
