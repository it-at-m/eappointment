import React from 'react'

import * as Inputs from './inputs'
import Errors from './errors'
const { Label, FormGroup, Controls, Description } = Inputs
import {range} from '../../../lib/utils'

const renderBody = (data, errors, onChange, onSave, onDelete) => {
    console.log('render Form Body', data)
    return (
        <div>
            <Errors {...{ errors }}/>
            <form>
                <fieldset>
                    <FormGroup>
                        <Label>Anmerkung</Label>
                        <Controls>
                            <Inputs.Text name="description" value={data.description} {...{onChange}} />
                            <Description>(optionale Angabe zur Kennzeichnung des Termins)</Description>
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
                                    {value: "5", name: "jede 5. Woche im Monat"}
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
                                    attributes={{maxLength: 3}}
                                    {...{ onChange }}/> min.
                                <Description>(Abstand zweier aufeinander folgender Termine)</Description>
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label></Label>
                            <Controls>
                                <Label>
                                    <Inputs.Checkbox name="multipleSlotAllowed"
                                        checked={Boolean(parseInt(data.multipleSlotAllowed))} {...{ onChange }} />
                            Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen
                                    <Description>(wie in der Dienstleistungsdatenbank konfiguriert)</Description>
                                </Label>
                            </Controls>
                        </FormGroup>

                        <FormGroup>
                            <Label>Buchbar:</Label>
                            <Controls>
                                von
                                <Inputs.Text name="open_from"
                                    width="1"
                                    value={data.open_from}
                                    attributes={{placeholder: data.scope.preferences.appointment.startInDaysDefault}}
                                    {...{ onChange }}
                                />
                                bis
                                <Inputs.Text name="open_to"
                                    width="1"
                                    value={data.open_to}
                                    attributes={{placeholder: data.scope.preferences.appointment.endInDaysDefault}}
                                    {...{ onChange }}
                                />
                                Tage im voraus
                                <Description>(0 = Einstellungen vom Standort übernehmen)</Description>
                            </Controls>
                        </FormGroup>
                </fieldset>

                <fieldset>
                    <legend>{data.type === "appointment" ? "Terminarbeitsplätze" : "Arbeitsplätze"}</legend>
                    <FormGroup>
                        <Label>Insgesamt</Label>
                        <Controls>
                            <Inputs.Text name="workstationCount_intern"
                                value={data.workstationCount_intern}
                                {...{ onChange }}
                                attributes={{maxLength: "2", width: "1"}} />
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

                <div className="form-actions">
                    <button className="button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button>
                    <div className="right">
                        <button className={data.__modified ? "button-save" : "btn"}
                            type="save"
                            value="save"
                            onClick={onSave}>Schließen</button>
                    </div>
                </div>
            </form>
        </div>
    )
}

export default renderBody 
