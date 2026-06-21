import { check } from "k6";
import { trackedRequest, extractId } from "../helpers.js";
import { BASE_URL, AUTH_HEADERS } from "../config.js";
import {
    generateAchievementData,
    generateAchievementUpdateData,
} from "../seeders/achievements.js";

export function testAchievements(ids) {
    let res, resourceId;
    const achFields = generateAchievementData(ids);

    res = trackedRequest(
        "POST",
        `${BASE_URL}/v1/achievements`,
        achFields,
        { ...AUTH_HEADERS, Accept: "application/json" },
        "achievements",
    );
    check(res, {
        "Achievements CREATE": (r) => r.status === 201 || r.status === 200,
    });
    resourceId = extractId(res);

    if (resourceId) {
        res = trackedRequest(
            "PUT",
            `${BASE_URL}/v1/achievements/${resourceId}`,
            JSON.stringify(generateAchievementUpdateData(ids)),
            {
                ...AUTH_HEADERS,
                Accept: "application/json",
                "Content-Type": "application/json",
            },
            "achievements",
        );
        check(res, { "Achievements UPDATE": (r) => r.status === 200 });

        res = trackedRequest(
            "DELETE",
            `${BASE_URL}/v1/achievements/${resourceId}`,
            null,
            AUTH_HEADERS,
            "achievements",
        );
        check(res, { "Achievements DELETE": (r) => r.status === 200 });
    }
}

export function testAchievementsUpdate(ids) {
    let res, resourceId;
    const achFields = generateAchievementData(ids);

    // Create resource to get ID
    res = trackedRequest(
        "POST",
        `${BASE_URL}/v1/achievements`,
        achFields,
        { ...AUTH_HEADERS, Accept: "application/json" },
        "achievements",
    );
    resourceId = extractId(res);

    if (resourceId) {
        // Perform multiple updates to stress the update endpoint
        for (let i = 0; i < 3; i++) {
            res = trackedRequest(
                "PUT",
                `${BASE_URL}/v1/achievements/${resourceId}`,
                JSON.stringify(generateAchievementUpdateData(ids)),
                {
                    ...AUTH_HEADERS,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                "achievements",
            );
            check(res, { "Achievements UPDATE": (r) => r.status === 200 });
        }

        // Cleanup
        res = trackedRequest(
            "DELETE",
            `${BASE_URL}/v1/achievements/${resourceId}`,
            null,
            AUTH_HEADERS,
            "achievements",
        );
        check(res, { "Achievements DELETE": (r) => r.status === 200 });
    }
}
