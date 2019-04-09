import { Role } from 'testcafe';
import Config from './config';


export const userRegular = Role(
    'https://127.0.0.1:8443/terminvereinbarung/admin/workstation/quicklogin?scope=141&workstation=test&loginName=testuser&password=vorschau&url=/workstation/select/',
    async t => {
        await t
    }
);
