import React from 'react'

import * as Inputs from '../../../lib/inputs'
import Errors from './errors'
const { Label, FormGroup, Controls, Description } = Inputs
import { range } from '../../../lib/utils'
import { weekDayList, availabilitySeries, availabilityTypes } from '../helpers'

const renderBody = (data, errors, onChange, onSave, onPublish, onDelete, onAbort, setErrorRef) => {
    console.log(data)
    return (
        <div>
            <div ref={setErrorRef}>
                <Errors {...{ errors }} />
            </div>
            <form className="form--base">
                
                    <FormGroup>
                        <Label attributes={{"htmlFor": "AvDayDescription"}} value="Anmerkung"></Label> 
                        <Controls>
                            <Inputs.Text 
                                attributes={{ "id": "AvDayDescription", "aria-describedby": "help_AvDayDescription" }}
                                name="description" 
                                value={data.description} 
                                {...{ onChange }} 
                            />
                            <Description attributes={{ "id": "help_AvDayDescription" }} value="Optionale Angabe zur Kennzeichnung des Termins.">
                                {data.id ? " Die ID der Öffnungszeit ist " + data.id : " Die Öffnungszeit hat noch keine ID"}
                            </Description>
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label attributes={{"htmlFor": "AvDayType"}} value="Typ"></Label>
                        <Controls>
                            <Inputs.Select name="type"
                                attributes={{ disabled: data.id ? 'disabled' : null, "id": "AvDayType" }}
                                value={data.type ? data.type : 0} {...{ onChange }}
                                options={availabilityTypes} />
                        </Controls>
                    </FormGroup>
                    <FormGroup>
                        <Label attributes={{"htmlFor": "AvDaySeries"}} value="Serie"></Label>
                        <Controls>
                            <Inputs.Select 
                                name="repeat"
                                attributes={{ "id": "AvDaySeries" }} 
                                value={data.repeat} {...{ onChange }}
                                options={availabilitySeries} />
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
                                    boxes={weekDayList} />
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
                            <Label attributes={{"htmlFor": "AvDayStartdate", "className": "light"}} value="Startdatum:"></Label> 
                            <Controls>
                                <Inputs.Date attributes={{ "id": "AvDayStartdate" }} name="startDate" value={data.startDate} {...{ onChange }} />
                            </Controls>
                            <Label attributes={{"htmlFor": "AvDayEnddate", "className": "light"}} value="Enddatum:"></Label> 
                            <Controls>
                                <Inputs.Date attributes={{ "id": "AvDayEnddate" }} name="endDate" value={data.endDate} {...{ onChange }} />
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Zeitschlitz</legend>
                        <FormGroup inline={true}>    
                            <Controls>
                                <Inputs.Text name="slotTimeInMinutes"
                                    value={data.slotTimeInMinutes}
                                    width="2"
                                    attributes={{ maxLength: 3, "id": "AvDaySlottime" }}
                                    {...{ onChange }} />
                                <Label attributes={{"htmlFor": "AvDaySlottime", "className": "light"}} value="&nbsp;Minuten Abstand zweier aufeinander folgender Termine"></Label>
                            </Controls>
                        </FormGroup>
                        <FormGroup inline={true} className="form-check">    
                            <Controls>
                                <Inputs.Checkbox label="Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen" name="multipleSlotsAllowed" value={true === data.multipleSlotsAllowed}
                                checked={true === data.multipleSlotsAllowed} {...{ onChange }} />
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Buchbar</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpenfrom", "className": "light"}} value="von"></Label> 
                                <Inputs.Text name="open_from"
                                    width="1"
                                    value={data.open_from}
                                    attributes={{ placeholder: data.scope.preferences.appointment.startInDaysDefault, "id": "AvDayOpenfrom", "aria-describedby": "help_AvDayOpenfromto" }}
                                    {...{ onChange }}
                                />
                            </Controls>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpento", "className": "light"}} value="bis">
                                </Label> 
                                    <Inputs.Text name="open_to"
                                    width="1"
                                    value={data.open_to}
                                    attributes={{ placeholder: data.scope.preferences.appointment.endInDaysDefault, "id": "AvDayOpento", "aria-describedby": "help_AvDayOpenfromto" }}
                                    {...{ onChange }}
                                />
                                <span aria-hidden="true">Tage im voraus</span>
                            </Controls>
                            <Description attributes={{ "id": "help_AvDayOpenfromto" }}>Tage im voraus (Keine Eingabe bedeutet die Einstellungen vom Standort zu übernehmen).</Description>
                        </FormGroup>
                        
                    </fieldset>

                <fieldset>
                    {data.type !== 'openinghours' ? <legend>Terminarbeitsplätze</legend> : null}
                    {data.type !== 'openinghours' ?
                        <div>
                            <FormGroup>
                                <Label attributes={{"htmlFor": "WsCountIntern"}} value="Insgesamt"></Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_intern"
                                        value={data.workstationCount_intern}
                                        attributes={{"id": "WsCountIntern"}}
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
                                <Label attributes={{"htmlFor": "WsCountCallcenter"}} value="Callcenter"></Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_callcenter"
                                        value={data.workstationCount_callcenter}
                                        attributes={{"id": "WsCountCallcenter", "aria-describedby": "help_WsCountCallcenter"}}
                                        {...{ onChange }}
                                        options={range(0, data.workstationCount_intern).map(n => {
                                            return {
                                                value: `${n}`,
                                                name: `${n}`
                                            }
                                        })} />
                                    <Description attributes={{"id": "help_WsCountCallcenter"}} value="Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden."></Description>
                                </Controls>
                            </FormGroup>

                            <FormGroup>
                                <Label attributes={{"htmlFor": "WsCountPublic"}} value="Internet"></Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_public"
                                        value={data.workstationCount_public}
                                        attributes={{"id": "WsCountPublic", "aria-describedby": "help_WsCountPublic"}}
                                        {...{ onChange }}
                                        options={range(0, data.workstationCount_intern).map(n => {
                                            return {
                                                value: `${n}`,
                                                name: `${n}`
                                            }
                                        })} />
                                    <Description attributes={{"htmlFor": "help_WsCountPublic"}} value="Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden."></Description>
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
