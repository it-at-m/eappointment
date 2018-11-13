import React from 'react'

import * as Inputs from '../../../lib/inputs'
import Errors from './errors'
const { Label, FormGroup, Controls, Description } = Inputs
import {range} from '../../../lib/utils'

const renderBody = (data, errors, onChange, onSave, onPublish, onDelete) => {
    return (
        <div>
            <Errors {...{ errors }}/>
            <form>
                <fieldset>
                    <FormGroup>
                        <Label>Anmerkung</Label>
                        <Controls>
                            <Inputs.Text name="description" value={data.description} {...{onChange}} />
                            <Description>(optionale Angabe zur Kennzeichnung des Termins)
                                {data.id ? " Die ID der Öffnungszeit ist " + data.id : " Die Öffnungszeit hat noch keine ID"}
                            </Description>
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label>Typ</Label>
                        <Controls>
                            <Inputs.Select name="type"
                                attributes={{disabled: data.id ? 'disabled' : null}}
                                value={data.type}
                                {...{ onChange}}
                                options={[
                                    {value: "", name: "--Bitte wählen--"},
                                    {value: "openinghours", name: "Spontankunden"},
                                    {value: "appointment", name: "Terminkunden"},
                                ]} {...{ onChange }}/>
                        </Controls>
                    </FormGroup>

                    <FormGroup>
                        <Label>Serie</Label>
                        <Controls>
                            <Inputs.Select name="repeat"
                                value={data.repeat}
                                {...{ onChange }}
                                options={[
                                    {value: "0", name: "einmaliger Termin"},
                                    {value: "-1", name: "jede Woche"},
                                    {value: "-2", name: "alle 2 Wochen"},
                                    {value: "-3", name: "alle 3 Wochen"},
                                    {value: "1", name: "jede 1. Woche im Monat"},
                                    {value: "2", name: "jede 2. Woche im Monat"},
                                    {value: "3", name: "jede 3. Woche im Monat"},
                                    {value: "4", name: "jede 4. Woche im Monat"},
                                    {value: "5", name: "jede letzte Woche im Monat"}
                                ]} />
                        </Controls>
                    </FormGroup>

                    <FormGroup>
                        <Label>Wochentage:</Label>
                        <Controls>
                            <Inputs.CheckboxGroup name="weekday"
                                value={data.weekday}
                                inline={true}
                                {...{ onChange }}
                                boxes={[
                                    {value: "monday", label: "Mo"},
                                    {value: "tuesday", label: "Di"},
                                    {value: "wednesday", label: "Mi"},
                                    {value: "thursday", label: "Do"},
                                    {value: "friday", label: "Fr"},
                                    {value: "saturday", label: "Sa"},
                                        {value: "sunday", label: "So"}
                                    ]} />
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label>Uhrzeit:</Label>
                            <Controls>
                        von <Inputs.Time name="startTime" value={data.startTime} {...{ onChange}} /> Uhr
                        bis <Inputs.Time name="endTime" value={data.endTime} {...{ onChange}} /> Uhr
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label>Datum:</Label>
                            <Controls>
                        Startdatum: <Inputs.Date name="startDate" value={data.startDate} {...{ onChange}}/>
                        {" "}
                        Enddatum: <Inputs.Date name="endDate" value={data.endDate} {...{ onChange }}/>
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label>Zeitschlitz:</Label>
                            <Controls>
                                <Inputs.Text name="slotTimeInMinutes"
                                    value={data.slotTimeInMinutes}
                                    width="1"
                                    attributes={{maxLength: 3}}
                                    {...{ onChange }}/>
                                Minuten Abstand zweier aufeinander folgender Termine
                                <Label>
                                    Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen:&nbsp;
                                    <Inputs.Checkbox name="multipleSlotsAllowed"
                                        checked={true == data.multipleSlotsAllowed} {...{ onChange }} />
                                </Label>
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label>Buchbar:</Label>
                            <Controls>
                                von&nbsp;
                                <Inputs.Text name="open_from"
                                    width="1"
                                    value={data.open_from}
                                    attributes={{placeholder: data.scope.preferences.appointment.startInDaysDefault}}
                                    {...{ onChange }}
                                />
                                &nbsp;bis&nbsp;
                                <Inputs.Text name="open_to"
                                    width="1"
                                    value={data.open_to}
                                    attributes={{placeholder: data.scope.preferences.appointment.endInDaysDefault}}
                                    {...{ onChange }}
                                />&nbsp;
                                Tage im voraus
                                <Description>(Keine Eingabe = Einstellungen vom Standort übernehmen)</Description>
                            </Controls>
                        </FormGroup>
                </fieldset>

                <fieldset>
                    <legend>{data.type === "appointment" ? "Terminarbeitsplätze" : "Arbeitsplätze"}</legend>
                    <FormGroup>
                        <Label>Insgesamt</Label>
                        <Controls>
                            <Inputs.Select name="workstationCount_intern"
                                value={data.workstationCount_intern}
                                {...{ onChange }}
                                options={range(0, 50).map(n => {
                                        return {
                                            value: `${n}`,
                                            name: `${n}`
                                        }
                                    })}/>
                        </Controls>
                    </FormGroup>

                    {data.type !== 'openinghours' ?
                    <FormGroup>
                        <Label>Callcenter</Label>
                        <Controls>
                            <Inputs.Select name="workstationCount_callcenter"
                                value={data.workstationCount_callcenter}
                                {...{ onChange }}
                                options={range(0, data.workstationCount_intern).map(n => {
                                        return {
                                            value: `${n}`,
                                            name: `${n}`
                                        }
                                    })}/>
                            <Description>wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.</Description>
                        </Controls>
                    </FormGroup> : null }

                    {data.type !== 'openinghours' ?
                    <FormGroup>
                        <Label>Internet</Label>
                        <Controls>
                            <Inputs.Select name="workstationCount_public"
                                value={data.workstationCount_public}
                                {...{ onChange }}
                                options={range(0, data.workstationCount_callcenter).map(n => {
                                        return {
                                            value: `${n}`,
                                            name: `${n}`
                                        }
                                    })}/>
                            <Description>wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden.</Description>
                        </Controls>
                    </FormGroup> : null }
                </fieldset>
                {console.log(data)}
                <div className="form-actions">
                  {data.id ?  <button className="button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button> :
                   <button className="button-abort" id="availability-abort" value="abort">Abbrechen</button>}         
                    <div className="right">
                        <button className={data.__modified ? "button-new btn--b3igicon" : "btn"}
                            type="save"
                            value="save"
                            onClick={onSave}>{data.__modified? "+ merken und später aktivieren" : "Schließen"}</button>
                        {data.__modified ?
                        <button className="button-save"
                            type="save"
                            value="publish"
                            onClick={onPublish}>Alle Änderungen aktivieren</button> : null }
                    </div>
                </div>
            </form>
        </div>
    )
}

export default renderBody 
