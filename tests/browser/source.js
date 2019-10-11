/* global fixture, test */
import { Selector } from 'testcafe';
import Config from './lib/config';
import { userSuper } from './lib/roles';
import { getPageUrl } from './lib/client';

fixture `Mandanten Workflow komplett`
    .page `${Config.baseUrl}/`
    .httpAuth(Config.credentials)
;

test('NewSource', async t => {
    const currentDate = new Date();
    const testName = "Test " + currentDate.toISOString();
    const testLabel = "t" + ("" + Math.floor(Date.now() / 1000)).substring(3, 11);
    const insertOpt = {replace: true, paste: true};
    await t
        .useRole(userSuper)
        .setNativeDialogHandler(() => true) //disable dialogs
        .navigateTo(`${Config.baseUrl}/source/`)
        .expect(Selector('.source-list td.source--source').withText('dldb').exists).ok('A reference to the DLDB should be visible')
        .click('.type-new')
        .typeText('input[name=label]', testName, insertOpt)
        .typeText('input[name=source]', testLabel, insertOpt)
        .typeText('input[name="contact[name]"]', "Testcafe", insertOpt)
        .typeText('input[name="requests[0][name]"]', "Testen", insertOpt)
        .typeText('input[name="providers[0][name]"]', "Station", insertOpt)
        .click('.requestrelation--new')
        .click('button.type-save')
        .wait(100)
        .click('button.type-save')
        .navigateTo(`${Config.baseUrl}/source/`)
        .expect(Selector('.source-list td.source--source').withText(testLabel).exists).ok('A reference to the testlabel should be visible')
        .navigateTo(`${Config.baseUrl}/department/74/scope/`)
        .expect(Selector('select[name="provider[source]"] option').withAttribute('value', testLabel).exists).ok('An option to the testlabel should be selectable')
        .click(Selector('select[name="provider[source]"]'))
        .click(Selector('select[name="provider[source]"] option').withAttribute('value', testLabel))
        .click(Selector('select[name="provider[id]"]'))
        .click(Selector('select[name="provider[id]"] option').withText('Station'))
        .typeText('input[name=shortName]', testLabel, insertOpt)
        .click('button.type-save')
        .navigateTo(`${Config.baseUrl}/owner/`)
        .expect(Selector('.owner-overview_scope-list li a').withText(testLabel).exists).ok('A link to the testlabel should be visible')
        .click(Selector('.owner-overview_scope-list li a').withText(testLabel).nextSibling())
        .click('.availability-monthtable td.today a.month-item')
        .click('.availability-timetable .header_right button.button-new')
        .typeText('.availability-form input[name=description]', "morning for " + testLabel, insertOpt)
        .click('.availability-form select[name=type]')
        .click('.availability-form select[name=type] option[value=appointment]')
        .click('.availability-form select[name=startTime--hour]')
        .click('.availability-form select[name=startTime--hour] option[value="7"]')
        .click('.availability-form select[name=startTime--minute]')
        .click('.availability-form select[name=startTime--minute] option[value="40"]')
        .click('.availability-form select[name=endTime--hour]')
        .click('.availability-form select[name=endTime--hour] option[value="21"]')
        .click('.availability-form select[name=endTime--minute]')
        .click('.availability-form select[name=endTime--minute] option[value="10"]')
        .click('.availability-form select[name=workstationCount_intern]')
        .click('.availability-form select[name=workstationCount_intern] option[value="1"]')
        .click('button.button-save[value=publish]')
        .click('.user-workstation a')
        .click('select[name=scope]')
        .click(Selector('select[name=scope] option').withText(testLabel))
        .click('button.type-login')
    ;
});
