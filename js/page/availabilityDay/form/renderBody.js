import React from 'react'

import * as Inputs from '../../../lib/inputs'
import Errors from './errors'
const { Label, FormGroup, Controls, Description } = Inputs
import { range } from '../../../lib/utils'

const renderBody = (data, errors, onChange, onSave, onPublish, onDelete, onAbort, setErrorRef) => {
    return (
        <div>
            <div ref={setErrorRef}>
                <Errors {...{ errors }} />
            </div>
            <form>
                
                    <FormGroup>
                        <Label>Anmerkung</Label>
                        <Controls>
                            <Inputs.Text name="description" value={data.description} {...{ onChange }} />
                            <Description>(optionale Angabe zur Kennzeichnung des Termins)
                                {data.id ? " Die ID der Öffnungszeit ist " + data.id : " Die Öffnungszeit hat noch keine ID"}
                            </Description>
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label>Typ</Label>
                        <Controls>
                            <Inputs.Select name="type"
                                attributes={{ disabled: data.id ? 'disabled' : null }}
                                value={data.type} {...{ onChange }}
                                options={[
                                    { value: "", name: "--Bitte wählen--" },
                                    { value: "openinghours", name: "Spontankunden" },
                                    { value: "appointment", name: "Terminkunden" },
                                ]} />
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label>Serie</Label>
                        <Controls>
                            <Inputs.Select name="repeat"
                                value={data.repeat} {...{ onChange }}
                                options={[
                                    { value: "0", name: "einmaliger Termin" },
                                    { value: "-1", name: "jede Woche" },
                                    { value: "-2", name: "alle 2 Wochen" },
                                    { value: "-3", name: "alle 3 Wochen" },
                                    { value: "1", name: "jede 1. Woche im Monat" },
                                    { value: "2", name: "jede 2. Woche im Monat" },
                                    { value: "3", name: "jede 3. Woche im Monat" },
                                    { value: "4", name: "jede 4. Woche im Monat" },
                                    { value: "5", name: "jede letzte Woche im Monat" }
                                ]} />
                        </Controls>
                    </FormGroup>

                    <fieldset>
                        <legend className="label">Wochentage</legend>
                        <FormGroup>
                            <Controls>
                                <Inputs.CheckboxGroup name="weekday"
                                    value={data.weekday}
                                    inline={true}
                                    {...{ onChange }}
                                    boxes={[
                                        { value: "monday", label: "Mo" },
                                        { value: "tuesday", label: "Di" },
                                        { value: "wednesday", label: "Mi" },
                                        { value: "thursday", label: "Do" },
                                        { value: "friday", label: "Fr" },
                                        { value: "saturday", label: "Sa" },
                                        { value: "sunday", label: "So" }
                                    ]} />
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Uhrzeit</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <span>von</span> <Inputs.Time name="startTime" value={data.startTime} {...{ onChange }} /> Uhr
                            </Controls>
                            <Controls>
                                <span>bis</span> <Inputs.Time name="endTime" value={data.endTime} {...{ onChange }} /> Uhr
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Datum</legend>
                        <FormGroup inline={true}>
                            <span>Startdatum:</span> 
                            <Controls>
                                <Inputs.Date name="startDate" value={data.startDate} {...{ onChange }} />
                            </Controls>
                            <span>Enddatum:</span>
                            <Controls>
                                <Inputs.Date name="endDate" value={data.endDate} {...{ onChange }} />
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Zeitschlitz</legend>
                        <FormGroup inline={true}>    
                            <Controls>
                                <Inputs.Text name="slotTimeInMinutes"
                                    value={data.slotTimeInMinutes}
                                    width="1"
                                    attributes={{ maxLength: 3 }}
                                    {...{ onChange }} />
                                Minuten Abstand zweier aufeinander folgender Termine
                                    
                            </Controls>
                        </FormGroup>
                        <FormGroup inline={true} className="form-check">    
                            <Controls>
                                <Label>
                                    <Inputs.Checkbox name="multipleSlotsAllowed"
                                        checked={true == data.multipleSlotsAllowed} {...{ onChange }} />
                                    Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen
                                </Label>
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Buchbar</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <span>von</span>
                                    <Inputs.Text name="open_from"
                                    width="1"
                                    value={data.open_from}
                                    attributes={{ placeholder: data.scope.preferences.appointment.startInDaysDefault }}
                                    {...{ onChange }}
                                />
                            </Controls>
                            <Controls>
                                <span>bis</span>
                                    <Inputs.Text name="open_to"
                                    width="1"
                                    value={data.open_to}
                                    attributes={{ placeholder: data.scope.preferences.appointment.endInDaysDefault }}
                                    {...{ onChange }}
                                />
                                <span>Tage im voraus</span>
                            </Controls>
                            <Description>(Keine Eingabe = Einstellungen vom Standort übernehmen)</Description>
                        </FormGroup>
                        
                    </fieldset>

                <fieldset>
                    {data.type !== 'openinghours' ? <legend>Terminarbeitsplätze</legend> : null}
                    {data.type !== 'openinghours' ?
                        <div>
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
                                        })} />
                                </Controls>
                            </FormGroup>

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
                                        })} />
                                    <Description>wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.</Description>
                                </Controls>
                            </FormGroup>

                            <FormGroup>
                                <Label>Internet</Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_public"
                                        value={data.workstationCount_public}
                                        {...{ onChange }}
                                        options={range(0, data.workstationCount_intern).map(n => {
                                            return {
                                                value: `${n}`,
                                                name: `${n}`
                                            }
                                        })} />
                                    <Description>wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden.</Description>
                                </Controls>
                            </FormGroup>
                        </div>
                        : null}
                </fieldset>

                <div className="form-actions">
                   
                    <button className="button button--destructive button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button>
                    <button className="button btn" type="abort" onClick={onAbort}>Abbrechen</button>

                    {data.__modified ?
                        <button className="button button-new btn--b3igicon"
                            type="save"
                            value="save"
                            onClick={onSave}><i className="far fa-bookmark" aria-hidden="true"></i> merken und später aktivieren
                        </button> : null}
                    {data.__modified ?
                        <button className="button button--positive button-save"
                            type="save"
                            value="publish"
                            onClick={onPublish}>Alle Änderungen aktivieren
                        </button> : null}
                
                </div>
            </form>
        </div>
    )
}

export default renderBody 
