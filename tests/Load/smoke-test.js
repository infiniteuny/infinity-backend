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

// Endpoint modules
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
// Smoke Test Options - All scenarios with minimal VU (1 each)
// Verifies all endpoints work correctly before running breakpoint load test
// ============================================================================
export const options = {
    scenarios: {
        // Volume Grinders (Reads) - 80% of traffic
        reads_users: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readUsers",
            startTime: "0s",
        },
        reads_user_permissions: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readUserPermissions",
            startTime: "0s",
        },
        reads_competitions: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readCompetitions",
            startTime: "0s",
        },
        reads_configs: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readConfigs",
            startTime: "0s",
        },
        reads_community_groups: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readCommunityGroups",
            startTime: "0s",
        },
        reads_testimonials: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "readTestimonials",
            startTime: "0s",
        },

        // Financial Bottlenecks (Creates) - 15% of traffic
        creates_fund_applications: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "createFundApplications",
            startTime: "0s",
        },
        creates_achievements: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "createAchievements",
            startTime: "0s",
        },
        creates_teams: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "createTeams",
            startTime: "0s",
        },

        // Concurrency Traps (Updates) - 5% of traffic
        updates_fund_applications: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "updateFundApplications",
            startTime: "0s",
        },
        updates_achievements: {
            executor: "constant-arrival-rate",
            rate: 1,
            timeUnit: "1s",
            duration: "30s",
            preAllocatedVUs: 1,
            maxVUs: 1,
            exec: "updateAchievements",
            startTime: "0s",
        },
    },
    thresholds: {
        http_req_duration: ["p(95)<5000"],
        http_req_failed: ["rate<0.1"],
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

        // Create all prerequisite resources
        ids.degreeId = createResource(
            "POST",
            `${BASE_URL}/v1/degrees`,
            JSON.stringify(generateDegreeData()),
            JSON_HEADERS,
            "Degree",
        );
        ids.facultyId = createResource(
            "POST",
            `${BASE_URL}/v1/faculties`,
            JSON.stringify(generateFacultyData()),
            JSON_HEADERS,
            "Faculty",
        );

        if (ids.degreeId && ids.facultyId) {
            ids.majorId = createResource(
                "POST",
                `${BASE_URL}/v1/majors`,
                JSON.stringify(generateMajorData(ids)),
                JSON_HEADERS,
                "Major",
            );
        }

        ids.competitionOrganizerTypeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-organizer-types`,
            JSON.stringify(generateCompetitionOrganizerTypeData()),
            JSON_HEADERS,
            "Competition Organizer Type",
        );
        ids.competitionOutputId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-outputs`,
            JSON.stringify(generateCompetitionOutputData()),
            JSON_HEADERS,
            "Competition Output",
        );
        ids.competitionRankId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-ranks`,
            JSON.stringify(generateCompetitionRankData()),
            JSON_HEADERS,
            "Competition Rank",
        );
        ids.competitionScaleId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-scales`,
            JSON.stringify(generateCompetitionScaleData()),
            JSON_HEADERS,
            "Competition Scale",
        );
        ids.competitionTeamTypeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-team-types`,
            JSON.stringify(generateCompetitionTeamTypeData()),
            JSON_HEADERS,
            "Competition Team Type",
        );
        ids.competitionTimeRangeId = createResource(
            "POST",
            `${BASE_URL}/v1/competition-time-ranges`,
            JSON.stringify(generateCompetitionTimeRangeData()),
            JSON_HEADERS,
            "Competition Time Range",
        );
        ids.competitionId = createResource(
            "POST",
            `${BASE_URL}/v1/competitions`,
            JSON.stringify(generateCompetitionData()),
            JSON_HEADERS,
            "Competition",
        );

        if (ids.competitionId && ids.competitionOrganizerTypeId) {
            ids.competitionInstanceId = createResource(
                "POST",
                `${BASE_URL}/v1/competition-instances`,
                generateCompetitionInstanceData(ids),
                { ...AUTH_HEADERS, Accept: "application/json" },
                "Competition Instance",
            );
        }

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
        }

        ids.communityGroupId = createResource(
            "POST",
            `${BASE_URL}/v1/community-groups`,
            generateCommunityGroupData(),
            { ...AUTH_HEADERS, Accept: "application/json" },
            "Community Group",
        );

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

        if (ids.userId && ids.competitionTeamTypeId) {
            ids.teamId = createResource(
                "POST",
                `${BASE_URL}/v1/teams`,
                JSON.stringify(generateTeamData(ids)),
                JSON_HEADERS,
                "Team",
            );
        }

        ids.permissionId = createResource(
            "POST",
            `${BASE_URL}/v1/permissions`,
            JSON.stringify({ name: `k6_perm_${uid()}`, guard_name: "api" }),
            JSON_HEADERS,
            "Permission",
        );

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
// Executor Functions - One per scenario
// ============================================================================
export function readUsers(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Users READ", () => testUsers(setupResult.ids));
    sleep(0.1);
}

export function readUserPermissions(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: User Permissions READ", () =>
        testUserPermissions(setupResult.ids),
    );
    sleep(0.1);
}

export function readCompetitions(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Competitions READ", () => testCompetitions());
    sleep(0.1);
}

export function readConfigs(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Configs READ", () => testConfigs());
    sleep(0.1);
}

export function readCommunityGroups(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Community Groups READ", () => testCommunityGroups());
    sleep(0.1);
}

export function readTestimonials(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Testimonials READ", () => testTestimonials());
    sleep(0.1);
}

export function createFundApplications(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Fund Applications CREATE", () =>
        testFundApplications(setupResult.ids),
    );
    sleep(0.1);
}

export function createAchievements(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Achievements CREATE", () =>
        testAchievements(setupResult.ids),
    );
    sleep(0.1);
}

export function createTeams(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Teams CREATE", () => testTeams(setupResult.ids));
    sleep(0.1);
}

export function updateFundApplications(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Fund Applications UPDATE", () =>
        testFundApplicationsUpdate(setupResult.ids),
    );
    sleep(0.1);
}

export function updateAchievements(setupResult) {
    if (!setupResult?.ids || setupResult.hasFailure) return;
    group("Smoke: Achievements UPDATE", () =>
        testAchievementsUpdate(setupResult.ids),
    );
    sleep(0.1);
}
