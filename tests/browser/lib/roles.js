import { Role } from 'testcafe';
import Config from './config';


export const userRegular = Role(
    Config.baseUrl + '/workstation/quicklogin?scope=141&workstation=test&loginName=testuser&password=vorschau&url=/workstation/select/',
    async t => {
        await t
    }
);

export const userSuper = Role(
    Config.baseUrl + '/workstation/quicklogin?scope=141&workstation=test&loginName=superuser&password=vorschau&url=/workstation/select/',
    async t => {
        await t
    }
);
