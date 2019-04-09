import { ClientFunction } from 'testcafe';
import { userRegular } from './lib/roles';
import Config from './lib/config';

const getPageUrl = ClientFunction(() => window.location.href);

fixture `Login`
    .page `${Config.baseUrl}/`;

test('Anmelden-Button', async t => {
    await t
        .useRole(userRegular)
        // logout before testing to avoid alread logged in message
        .navigateTo(`${Config.baseUrl}/logout/`)
        .typeText('input[name=loginName]', 'testuser')
        .typeText('input[name=password]', 'vorschau')
        .click('button.button-login')
        .expect(getPageUrl()).contains('workstation/select');
});
