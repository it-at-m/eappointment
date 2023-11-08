import React from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../../lib/inputs'
import AvailabilityDatePicker from './datepicker'
const { Label, FormGroup, Controls, Description } = Inputs
import { range } from '../../../lib/utils'
import { weekDayList, availabilitySeries, availabilityTypes, getDataValuesFromForm } from '../helpers'
import ErrorBar from '../errorBar'

const FormContent = (props) => {
    const {
        availabilityList, 
        data, 
        errorList, 
        conflictList,
        onChange, 
        today, 
        setErrorRef
    } = props;
    
    const calenderDisabled = data.type && data.slotTimeInMinutes ? false : true;
    const isUnsafedSpontaneous = data.id == 0;

    return (
        <div>
            <ErrorBar errorList={errorList} conflictList={conflictList} setErrorRef={setErrorRef} />
            <form className="form--base">
                <fieldset>
                    {isUnsafedSpontaneous ? 
                    <div className="message message-info">
                        Diese Öffnungszeit ist eine mit einer Termin-Öffnungszeit verbundenen Spontankunden-Öffnungszeit und hat noch keine eigene ID. Sie können diese Öffnungszeit erst einzeln aktualisieren, wenn sie die dazugehörige Termin-Öffnungszeit mit einmal initial aktualisiert haben.
                    </div> : null
                    }
                    <div className="panel--heavy">
                        <FormGroup>
                            <Label attributes={{"htmlFor": "AvDayDescription"}}>Anmerkung</Label> 
                            <Controls>
                                <Inputs.Text 
                                    attributes={{ "id": "AvDayDescription", "aria-describedby": "help_AvDayDescription" }}
                                    name="description" 
                                    value={data.description} 
                                    {...{ onChange }} 
                                />
                                <Description attributes={{ "id": "help_AvDayDescription" }}>Optionale Angabe zur Kennzeichnung des Termins. {data.id > 0 ? "Die ID der Öffnungszeit ist " + data.id : " Die Öffnungszeit hat noch keine ID"}.
                                </Description>
                            </Controls>
                        </FormGroup>
                        <FormGroup>
                            <Label attributes={{"htmlFor": "AvDayType"}}>Typ</Label>
                            <Controls>
                                <Inputs.Select name="type"
                                    attributes={{ disabled: data.id ? 'disabled' : null, "id": "AvDayType" }}
                                    value={data.type ? data.type : 0} {...{ onChange }}
                                    options={availabilityTypes} />
                                    <Description attributes={{ "id": "help_AvDayTypDescription" }}>Typ der Öffnungszeit.
                                </Description>
                            </Controls>
                        </FormGroup>
                    </div>
                </fieldset>
                <fieldset>
                    <div className="panel--heavy">
                        <legend className="label">Serie und Wochentage</legend>
                        <FormGroup>
                            <Label attributes={{"htmlFor": "AvDaySeries", "className": "light"}}>Serie</Label>
                            <Controls>
                                <Inputs.Select 
                                    name="repeat"
                                    attributes={{ "id": "AvDaySeries" }} 
                                    value={data.repeat} {...{ onChange }}
                                    options={availabilitySeries} />
                            </Controls>
                        </FormGroup>
                        <FormGroup>
                            <Label attributes={{"className": "light"}}>Wochentage</Label>
                            <Controls>
                                <Inputs.CheckboxGroup name="weekday"
                                    value={data.weekday}
                                    inline={true}
                                    {...{ onChange }}
                                    boxes={weekDayList}
                                    disabled={!data.repeat ? true : false}
                                />
                            </Controls>
                        </FormGroup>
                    </div>
                </fieldset>
                <fieldset>
                    <div className="panel--heavy">
                        <legend className="label">Terminabstand</legend>
                        <FormGroup inline={true}>    
                            <Controls>
                                <Inputs.Text name="slotTimeInMinutes"
                                    value={data.slotTimeInMinutes}
                                    width="2"
                                    attributes={{ disabled: 'disabled', maxLength: 3, "id": "AvDaySlottime" }}
                                    {...{ onChange }} />
                                <Label attributes={{"htmlFor": "AvDaySlottime", "className": "light"}}>&nbsp;Minuten Abstand zweier aufeinander folgender Termine</Label>
                            </Controls>
                        </FormGroup>
                        <FormGroup inline={true}>    
                            <Controls>
                                <Inputs.Checkbox name="multipleSlotsAllowed"
                                    checked={"1" == data.multipleSlotsAllowed} {...{ onChange }} 
                                    value="1"
                                    attributes={{ "id": "AvDayMultipleSlots" }}
                                />
                                <Label attributes={{"htmlFor": "AvDayMultipleSlots", "className": "light"}}>Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen</Label>
                            </Controls>
                        </FormGroup>
                    </div>
                </fieldset>
                
                <fieldset>
                    <div className="panel--heavy">
                        <legend className="label">Öffnungszeit</legend>
                        <AvailabilityDatePicker attributes={{
                            "id": "AvDatesStart", 
                            "availabilitylist": availabilityList,
                            "availability": getDataValuesFromForm(data, data.scope),
                            "today": today,
                            "kind": data.kind,
                            "disabled": calenderDisabled
                        }} name="startDate" {...{ onChange }} />
                        <AvailabilityDatePicker attributes={{
                            "id": "AvDatesEnd", 
                            "availabilitylist": availabilityList,
                            "availability": getDataValuesFromForm(data, data.scope),
                            "today": today,
                            "kind": data.kind,
                            "disabled": calenderDisabled
                        }} name="endDate" {...{ onChange }} />
                    </div>
                </fieldset>

                <fieldset>
                    <div className="panel--heavy">
                        <legend className="label">Buchbar</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpenfrom", "className": "light"}}>von</Label> 
                                <Inputs.Text name="open_from"
                                    width="2"
                                    value={data.open_from}
                                    attributes={{ placeholder: data.scope.preferences.appointment.startInDaysDefault, "id": "AvDayOpenfrom", "aria-describedby": "help_AvDayOpenfromto" }}
                                    {...{ onChange }}
                                />
                            </Controls>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpento", "className": "light"}}>bis</Label> 
                                    <Inputs.Text name="open_to"
                                    width="2"
                                    value={data.open_to}
                                    attributes={{ placeholder: data.scope.preferences.appointment.endInDaysDefault, "id": "AvDayOpento", "aria-describedby": "help_AvDayOpenfromto" }}
                                    {...{ onChange }}
                                />
                                <span aria-hidden="true"> Tage im voraus</span>
                            </Controls>
                            <Description attributes={{ "id": "help_AvDayOpenfromto" }}>Tage im voraus (Keine Eingabe bedeutet die Einstellungen vom Standort zu übernehmen).</Description>
                        </FormGroup>
                    </div>
                </fieldset>
                {data.type !== 'openinghours' ?
                <fieldset>
                    <div className="panel--heavy">
                        <legend>Terminarbeitsplätze</legend>
                        <div>
                            <FormGroup>
                                <Label attributes={{"htmlFor": "WsCountIntern"}}>Insgesamt</Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_intern"
                                        value={data.workstationCount_intern}
                                        attributes={{"id": "WsCountIntern"}}
                                        {...{ onChange }}
                                        options={range(0, 50).map(n => {
                                            let workstation = (n == 1) ? "Arbeitsplatz" : "Arbeitsplätze";
                                            return {
                                                title: `${n} ${workstation}`,
                                                value: `${n}`,
                                                name: `${n} ${workstation}`
                                            }
                                        })} />
                                </Controls>
                            </FormGroup>

                            <FormGroup>
                                <Label attributes={{"htmlFor": "WsCountCallcenter"}}>Callcenter</Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_callcenter"
                                        value={data.workstationCount_callcenter}
                                        attributes={{"id": "WsCountCallcenter", "aria-describedby": "help_WsCountCallcenter"}}
                                        {...{ onChange }}
                                        options={range(0, data.workstationCount_intern).map(n => {
                                            let workstation = (n == 1) ? "Arbeitsplatz" : "Arbeitsplätze";
                                            return {
                                                title: `${n} ${workstation}`,
                                                value: `${n}`,
                                                name: `${n} ${workstation}`
                                            }
                                        })} />
                                    <Description attributes={{"id": "help_WsCountCallcenter"}}>Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.</Description>
                                </Controls>
                            </FormGroup>

                            <FormGroup>
                                <Label attributes={{"htmlFor": "WsCountPublic"}}>Internet</Label>
                                <Controls>
                                    <Inputs.Select name="workstationCount_public"
                                        value={data.workstationCount_public}
                                        attributes={{"id": "WsCountPublic", "aria-describedby": "help_WsCountPublic"}}
                                        {...{ onChange }}
                                        options={range(0, data.workstationCount_intern).map(n => {
                                            let workstation = (n == 1) ? "Arbeitsplatz" : "Arbeitsplätze";
                                            return {
                                                title: `${n} ${workstation}`,
                                                value: `${n}`,
                                                name: `${n} ${workstation}`
                                            }
                                        })} />
                                    <Description attributes={{"htmlFor": "help_WsCountPublic"}}>Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden.</Description>
                                </Controls>
                            </FormGroup>
                        </div>
                    </div>
                </fieldset>
                : null}
            </form>
        </div>
    )
}

FormContent.propTypes = {
    availabilityList: PropTypes.array,
    errorList: PropTypes.object,
    conflictList: PropTypes.object,
    today: PropTypes.number,
    data: PropTypes.object,
    errors: PropTypes.object,
    onChange: PropTypes.func,
    setErrorRef: PropTypes.func,
}

export default FormContent 
