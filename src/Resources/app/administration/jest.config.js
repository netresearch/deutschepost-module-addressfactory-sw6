const {join, resolve} = require('path');
const {existsSync} = require('fs')

const {ADMIN_PATH} = process.env;
if (ADMIN_PATH === undefined) {
    console.error("Please define the ADMIN_PATH environment variable");
    process.exit(1);
}

module.exports = {
    preset: '@shopware-ag/jest-preset-sw6-admin',
    globals: {
// required, e.g. /www/sw6/platform/src/Administration/Resources/app/administration
        adminPath: ADMIN_PATH,
    },
    testMatch: [
        join(__dirname, 'test', '**', '*.spec.js'),
        join(__dirname, 'test', '**', '*.spec.ts')
    ]
};
