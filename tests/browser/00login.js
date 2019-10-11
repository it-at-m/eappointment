/* global fixture, test */
import Config from './lib/config';
import { getPageUrl } from './lib/client';

fixture `Login`
    .page `${Config.baseUrl}/`
    .httpAuth(Config.credentials)
;

test('Anmelden-Button', async t => {
    await t
        // logout before testing to avoid alread logged in message
        .typeText('input[name=loginName]', 'superuser') // use an account not for testing to avoid conflicts
        .typeText('input[name=password]', 'vorschau')
        .click('button.type-login')
        .navigateTo(`${Config.baseUrl}/logout/`)
        // if not logged in, no redirect for / appears, force it
        .typeText('input[name=loginName]', 'superuser')
        .typeText('input[name=password]', 'vorschau')
        .click('button.type-login')
        .expect(getPageUrl()).contains('workstation/select');
});
