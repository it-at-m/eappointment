import React from 'react'
import PropTypes from 'prop-types'
import * as Inputs from '../../../lib/inputs'
import AvailabilityDatePicker from './datepicker'
import Errors from './errors'
import Conflicts from './conflicts'
const { Label, FormGroup, Controls, Description } = Inputs
import { range } from '../../../lib/utils'
import { weekDayList, availabilitySeries, availabilityTypes, getDataValuesFromForm } from '../helpers'

const FormContent = (props) => {
    const {
        availabilityList, 
        data, 
        errorList, 
        conflictList,
        onChange, 
        today, 
        selectedDay, 
        setErrorRef
    } = props;

    const calenderDisabled = data.type && data.slotTimeInMinutes ? false : true
    return (
        <div>
            <div ref={setErrorRef}>
                <Errors {...{ errorList }} />
                <Conflicts {...{ conflictList }} />
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
                                    boxes={weekDayList}
                                    disabled={!data.repeat ? true : false}
                                />
                            </Controls>
                        </FormGroup>
                    </fieldset>

                    <fieldset>
                        <legend className="label">Terminabstand</legend>
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
                        <FormGroup inline={true}>    
                            <Controls>
                                <Inputs.Checkbox name="multipleSlotsAllowed"
                                    checked={true == data.multipleSlotsAllowed} {...{ onChange }} 
                                />
                                <Label value="Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen"></Label>
                            </Controls>
                        </FormGroup>
                    </fieldset>
                    
                    <fieldset>
                    <legend className="label">Öffnungszeit</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <AvailabilityDatePicker attributes={{
                                    "id": "AvDatesStart", 
                                    "availabilitylist": availabilityList,
                                    "availability": getDataValuesFromForm(data, data.scope),
                                    "today": today,
                                    "selectedday": data.startDate || selectedDay,
                                    "disabled": calenderDisabled
                                }} name="startDate" {...{ onChange }} />
                            </Controls>
                            <Controls>
                                <AvailabilityDatePicker attributes={{
                                    "id": "AvDatesEnd", 
                                    "availabilitylist": availabilityList,
                                    "availability": getDataValuesFromForm(data, data.scope),
                                    "today": today,
                                    "selectedday": data.endDate || selectedDay,
                                    "disabled": calenderDisabled
                                }} name="endDate" {...{ onChange }} />
                            </Controls>
                        </FormGroup> 
                    </fieldset>

                    <fieldset>
                        <legend className="label">Buchbar</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpenfrom", "className": "light"}} value="von"></Label> 
                                <Inputs.Text name="open_from"
                                    width="2"
                                    value={data.open_from}
                                    attributes={{ placeholder: data.scope.preferences.appointment.startInDaysDefault, "id": "AvDayOpenfrom", "aria-describedby": "help_AvDayOpenfromto" }}
                                    {...{ onChange }}
                                />
                            </Controls>
                            <Controls>
                                <Label attributes={{"htmlFor": "AvDayOpento", "className": "light"}} value="bis">
                                </Label> 
                                    <Inputs.Text name="open_to"
                                    width="2"
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
            </form>
        </div>
    )
}

FormContent.propTypes = {
    availabilityList: PropTypes.array,
    errorList: PropTypes.object,
    conflictList: PropTypes.object,
    today: PropTypes.number,
    selectedDay: PropTypes.number,
    data: PropTypes.object,
    errors: PropTypes.object,
    onChange: PropTypes.func,
    setErrorRef: PropTypes.func,
}

export default FormContent 
