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

    const hasEndTimePastError = Object.values(errorList)
        .some(error => error.itemList?.[0]?.[0]?.type === 'endTimePast');
    const calenderDisabled = data.type && data.slotTimeInMinutes ? false : true;
    const inputDisabled = hasEndTimePastError || calenderDisabled;

    const isUnsafedSpontaneous = data.id == 0;

    const filteredErrorList = Object.values(errorList)
        .filter(error => error.itemList?.[0]?.[0]?.type !== 'endTimePast')
        .reduce((acc, error) => {
            acc[error.id] = error;
            return acc;
        }, {});

    return (
        <div>
            <form className="form--base">
                <ErrorBar errorList={filteredErrorList} conflictList={conflictList} setErrorRef={setErrorRef} />
                <fieldset>
                    {isUnsafedSpontaneous ?
                        <section
                            style={{
                                position: 'relative',
                                borderColor: '#cccccc',
                                margin: '20px auto'
                            }}
                            className="dialog message"
                            role="alert"
                        >
                            <div style={{
                                position: 'absolute',
                                top: '-15px',
                                left: '7px',
                                backgroundColor: '#fcaa67',
                                width: '30px',
                                height: '30px',
                                display: 'flex',
                                justifyContent: 'center',
                                alignItems: 'center'
                            }}>
                                <i className="fas fa-exclamation-circle" title="Wichtiger Hinweis" aria-hidden="true" style={{ color: 'white' }} />
                            </div>
                            <h2 className="message__heading">Hinweis zur Termin-Öffnungszeit</h2>
                            <div className="message__body">
                                Diese Öffnungszeit ist eine mit einer Termin-Öffnungszeit verbundenen Spontankunden-Öffnungszeit und hat noch keine eigene ID. Sie können diese Öffnungszeit erst einzeln aktualisieren, wenn sie die dazugehörige Termin-Öffnungszeit mit einmal initial aktualisiert haben.
                            </div>
                        </section> : null
                    }
                    {hasEndTimePastError && Object.values(errorList).map(error => {
                        const endTimePastError = error.itemList?.[0]?.[0];
                        if (endTimePastError?.type === 'endTimePast') {
                            return (
                                <section
                                    key={error.id}
                                    style={{
                                        position: 'relative',
                                        borderColor: '#cccccc',
                                        margin: '20px auto'
                                    }}
                                    className="dialog message"
                                    role="alert"
                                >
                                    <div style={{
                                        position: 'absolute',
                                        top: '-15px',
                                        left: '7px',
                                        backgroundColor: '#fcaa67',
                                        width: '30px',
                                        height: '30px',
                                        display: 'flex',
                                        justifyContent: 'center',
                                        alignItems: 'center'
                                    }}>
                                        <i className="fas fa-exclamation-circle" title="Wichtiger Hinweis" aria-hidden="true" style={{ color: 'white' }} />
                                    </div>
                                    <h2 className="message__heading">Öffnungszeit liegt in der Vergangenheit</h2>
                                    <div className="message__body">
                                        {endTimePastError.message}
                                    </div>
                                </section>
                            );
                        }
                        return null;
                    })}
                    <div className="panel--heavy">
                        <FormGroup>
                            <Label attributes={{ "htmlFor": "AvDayDescription" }}>Anmerkung</Label>
                            <Controls>
                                <Inputs.Text
                                    attributes={{ "id": "AvDayDescription", "aria-describedby": "help_AvDayDescription", "disabled": inputDisabled }}
                                    name="description"
                                    value={data.description}
                                    {...{ onChange }}
                                />
                                <Description attributes={{ "id": "help_AvDayDescription" }}>Optionale Angabe zur Kennzeichnung des Termins. {data.id > 0 ? "Die ID der Öffnungszeit ist " + data.id : " Die Öffnungszeit hat noch keine ID"}.
                                </Description>
                            </Controls>
                        </FormGroup>
                        <FormGroup>
                            <Label attributes={{ "htmlFor": "AvDayType" }}>Typ</Label>
                            <Controls>
                                <Inputs.Select name="type"
                                    attributes={{ disabled: data.id ? 'disabled' : null, "id": "AvDayType", "disabled": inputDisabled }}
                                    value={data.type ? data.type : "appointment"} {...{ onChange }}
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
                            <Label attributes={{ "htmlFor": "AvDaySeries", "className": "light" }}>Serie</Label>
                            <Controls>
                                <Inputs.Select
                                    name="repeat"
                                    attributes={{ "id": "AvDaySeries", "disabled": inputDisabled }}
                                    value={data.repeat} {...{ onChange }}
                                    options={availabilitySeries} />
                            </Controls>
                        </FormGroup>
                        <FormGroup>
                            <Label attributes={{ "className": "light" }}>Wochentage</Label>
                            <Controls>
                                <Inputs.CheckboxGroup name="weekday"
                                    value={data.weekday}
                                    inline={true}
                                    {...{ onChange }}
                                    boxes={weekDayList}
                                    attributes={{ "disabled": inputDisabled }}
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
                                    attributes={{ disabled: 'disabled', maxLength: 3, "id": "AvDaySlottime", "disabled": inputDisabled }}
                                    {...{ onChange }} />
                                <Label attributes={{ "htmlFor": "AvDaySlottime", "className": "light" }}>&nbsp;Minuten Abstand zweier aufeinander folgender Termine</Label>
                            </Controls>
                        </FormGroup>
                        <FormGroup inline={true}>
                            <Controls>
                                <Inputs.Checkbox name="multipleSlotsAllowed"
                                    checked={"1" == data.multipleSlotsAllowed} {...{ onChange }}
                                    value="1"
                                    attributes={{ "id": "AvDayMultipleSlots", "disabled": inputDisabled }}
                                />
                                <Label attributes={{ "htmlFor": "AvDayMultipleSlots", "className": "light" }}>Die Dienstleistungen dürfen mehr als einen Zeitschlitz beanspruchen</Label>
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
                            "disabled": inputDisabled
                        }} name="startDate" {...{ onChange }} />
                        <AvailabilityDatePicker attributes={{
                            "id": "AvDatesEnd",
                            "availabilitylist": availabilityList,
                            "availability": getDataValuesFromForm(data, data.scope),
                            "today": today,
                            "kind": data.kind,
                            "disabled": inputDisabled
                        }} name="endDate" {...{ onChange }} />
                    </div>
                </fieldset>

                <fieldset>
                    <div className="panel--heavy">
                        <legend className="label">Buchbar</legend>
                        <FormGroup inline={true}>
                            <Controls>
                                <Label attributes={{ "htmlFor": "AvDayOpenfrom", "className": "light" }}>von</Label>
                                <Inputs.Text name="open_from"
                                    width="3"
                                    value={data.open_from}
                                    attributes={{ placeholder: data.scope.preferences.appointment.startInDaysDefault, "id": "AvDayOpenfrom", "aria-describedby": "help_AvDayOpenfromto", "disabled": inputDisabled }}
                                    {...{ onChange }}
                                />
                            </Controls>
                            <Controls>
                                <Label attributes={{ "htmlFor": "AvDayOpento", "className": "light" }}>bis</Label>
                                <Inputs.Text name="open_to"
                                    width="3"
                                    value={data.open_to}
                                    attributes={{ placeholder: data.scope.preferences.appointment.endInDaysDefault, "id": "AvDayOpento", "aria-describedby": "help_AvDayOpenfromto", "disabled": inputDisabled }}
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
                                    <Label attributes={{ "htmlFor": "WsCountIntern" }}>Insgesamt</Label>
                                    <Controls>
                                        <Inputs.Select name="workstationCount_intern"
                                            value={data.workstationCount_intern}
                                            attributes={{ "id": "WsCountIntern", "disabled": inputDisabled }}
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
                                    <Label attributes={{ "htmlFor": "WsCountCallcenter" }}>Callcenter</Label>
                                    <Controls>
                                        <Inputs.Select name="workstationCount_callcenter"
                                            value={data.workstationCount_callcenter}
                                            attributes={{ "id": "WsCountCallcenter", "aria-describedby": "help_WsCountCallcenter", "disabled": inputDisabled }}
                                            {...{ onChange }}
                                            options={range(0, data.workstationCount_intern).map(n => {
                                                let workstation = (n == 1) ? "Arbeitsplatz" : "Arbeitsplätze";
                                                return {
                                                    title: `${n} ${workstation}`,
                                                    value: `${n}`,
                                                    name: `${n} ${workstation}`
                                                }
                                            })} />
                                        <Description attributes={{ "id": "help_WsCountCallcenter" }}>Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.</Description>
                                    </Controls>
                                </FormGroup>

                                <FormGroup>
                                    <Label attributes={{ "htmlFor": "WsCountPublic" }}>Internet</Label>
                                    <Controls>
                                        <Inputs.Select name="workstationCount_public"
                                            value={data.workstationCount_public}
                                            attributes={{ "id": "WsCountPublic", "aria-describedby": "help_WsCountPublic", "disabled": inputDisabled }}
                                            {...{ onChange }}
                                            options={range(0, data.workstationCount_intern).map(n => {
                                                let workstation = (n == 1) ? "Arbeitsplatz" : "Arbeitsplätze";
                                                return {
                                                    title: `${n} ${workstation}`,
                                                    value: `${n}`,
                                                    name: `${n} ${workstation}`
                                                }
                                            })} />
                                        <Description attributes={{ "htmlFor": "help_WsCountPublic" }}>Wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Internet zur Verfügung gestellt werden.</Description>
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
