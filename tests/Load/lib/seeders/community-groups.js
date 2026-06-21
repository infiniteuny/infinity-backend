import http from "k6/http";
import { uid, IMAGE_BIN } from "../helpers.js";

export function generateCommunityGroupData() {
    const uniqueId = uid();

    return {
        name: `k6_community_group_${uniqueId}`,
        priority: Math.floor(Math.random() * 128),
        description: `k6 community group description ${uniqueId}`,
        logo: http.file(
            IMAGE_BIN,
            `community_group_${uniqueId}.jpg`,
            "image/jpeg",
        ),
        is_active: true,
    };
}

export function generateCommunityGroupUpdateData() {
    const uniqueId = uid();

    return {
        name: `k6_community_group_upd_${uniqueId}`,
        priority: Math.floor(Math.random() * 128),
        is_active: Math.random() > 0.5,
    };
}
