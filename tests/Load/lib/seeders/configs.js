import { uid } from "../helpers.js";

export function generateConfigData() {
    const uniqueId = uid();

    return {
        key: `k6_config_${uniqueId}`,
        value: `k6_value_${uniqueId}`,
        type: "STRING",
        is_private: false,
    };
}

export function generateConfigUpdateData() {
    const uniqueId = uid();

    return {
        value: `k6_config_upd_${uniqueId}`,
    };
}
