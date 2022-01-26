const {join, resolve} = require('path');
const {existsSync} = require('fs')

const {ADMIN_PATH} = process.env;
if (ADMIN_PATH === undefined) {
    console.error("Please define the ADMIN_PATH environment variable");
    process.exit(1);
}
let preparePath = resolve(join(ADMIN_PATH, '/test/_setup/prepare_environment.js'))
if (!existsSync(preparePath)) {
    preparePath = preparePath.replace('shopware/platform', 'shopware/administration');
}

module.exports = {
    preset: '@shopware-ag/jest-preset-sw6-admin',
    globals: {
// required, e.g. /www/sw6/platform/src/Administration/Resources/app/administration
        adminPath: ADMIN_PATH,
    },

    setupFilesAfterEnv: [
        preparePath,
    ],

    moduleNameMapper: {
        '^test(.*)$': '<rootDir>/test$1',
        vue$: 'vue/dist/vue.common.dev.js',
    },
};
