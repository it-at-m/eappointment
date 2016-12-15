import React, { PropTypes, Component } from 'react'

import Board from '../layouts/board'
import * as Inputs from './inputs'

import HeaderButtons from './headerButtons'

import validate from './validate'

import {range} from '../../../lib/utils'

const { Label, FormGroup, Controls, Description } = Inputs

const renderBody = (data, onChange, onSave, onDelete) => {
    return (
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
                            attributes={{disabled: data.type ? 'disabled' : null}}
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
                        von <Inputs.Text name="open_from" width="1" value={data.open_from} {...{ onChange }}/>
                        bis <Inputs.Text name="open_to" width="1" value={data.open_to} {...{ onChange }}/> Tage im voraus
                        <Description>(0 = Einstellungen vom Standort übernehmen)</Description>
                    </Controls>
                </FormGroup>
            </fieldset>

            <fieldset>
                <legend>Terminarbeitsplätze</legend>
                <FormGroup>
                    <Label>Insgesamt</Label>
                    <Controls>
                        <Inputs.Text name="workstationCount_intern"
                            value={data.workstationCount_intern}
                            {...{ onChange }}
                            attributes={{maxLength: "2", width: "1"}} />
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
                                })}/>
                        <Description>wieviele der insgesamt verfügbaren Terminarbeitsplätze sollen für das Callcenter zur Verfügung gestellt werden.</Description>
                    </Controls>
                </FormGroup>

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
                </FormGroup>
            </fieldset>

            <div className="form-actions">
                <button className="button-delete" type="delete" value="delete" onClick={onDelete}>Löschen</button>
                <div className="right">
                    <button className="button-save" type="save" value="save" onClick={onSave}>OK</button>
                </div>
            </div>
        </form>
    )
}

const getFirstLevelValues = data => {
    const {
        scope,
        description,
        startTime,
        endTime,
        startDate,
        endDate,
        multipleSlotsAllowed,
        id,
        type,
        slotTimeInMinutes
    } = data

    return {
        scope,
        description,
        startTime,
        endTime,
        startDate,
        endDate,
        multipleSlotsAllowed,
        id,
        type,
        slotTimeInMinutes
    }
}

const getFormValuesfromData = data => {

    const workstations = Object.assign({}, data.workstationCount)

    if (workstations.callcenter > workstations.intern) {
        workstations.callcenter = workstations.intern
    }

    if (workstations.public > workstations.callcenter) {
        workstations.public = workstations.callcenter
    }

    return Object.assign({}, getFirstLevelValues(data), {
        open_from: data.bookable.startInDays,
        open_to: data.bookable.endInDays,
        workstationCount_intern: workstations.intern,
        workstationCount_callcenter: workstations.callcenter,
        workstationCount_public: workstations.public,
        weekday: Object.keys(data.weekday).filter(key => parseInt(data.weekday[key], 10) > 0)
    })
}

const getDataValuesFromForm = form => {
    return Object.assign({}, getFirstLevelValues(form), {
        bookable: {
            startInDays: form.open_from,
            endInDays: form.open_to
        },
        workstationCount: {
            intern: form.workstationCount_intern,
            callcenter: form.workstationCount_callcenter,
            "public": form.workstationCount_public
        },
        weekday: form.weekday.reduce((carry, current) => {
            return Object.assign({}, carry, {[current]: 1})
        }, {})
    })
}

class AvailabilityForm extends Component {
    constructor(props) {
        super(props);

        this.state = {
            data: getFormValuesfromData(this.props.data),
            errors: {}
        }
    }

    componentWillReceiveProps(newProps) {
        this.setState({
            data: getFormValuesfromData(newProps.data)
        })
    }

    handleChange(name, value) {
        this.setState({
            data: Object.assign({}, this.state.data, {
                [name]: value
            })
        }, () => {
            this.props.onChange(getDataValuesFromForm(this.state.data))
        })
    }

    render() {
        const { data } = this.state
        const onChange = (name, value) => {
            this.handleChange(name, value)
        }

        const onSave = (ev) => {
            ev.preventDefault()
            const validationResult = validate(data)

            if (validationResult.valid) {
                this.props.onSave(getDataValuesFromForm(data))
            } else {
                console.log('errors', validationResult.errors)
                this.setState({ errors: validationResult.errors })
            }
        }

        const onDelete = ev => {
            ev.preventDefault()
            this.props.onDelete(getDataValuesFromForm(data))
        }

        const onCopy = ev => {
            ev.preventDefault()
            this.props.onCopy(getDataValuesFromForm(data))
        }

        const onException = ev => {
            ev.preventDefault()
            this.props.onException(getDataValuesFromForm(data))
        }

        const onEditInFuture = ev => {
            ev.preventDefault()
            this.props.onEditInFuture(getDataValuesFromForm(data))
        }

        return <Board title="Öffnungszeit bearbeiten"
                   headerRight={<HeaderButtons {...{ onCopy, onException, onEditInFuture}} />}
                   body={renderBody(data, onChange, onSave, onDelete)}
                   footer=""
                   className="availability-form" />
    }
}

AvailabilityForm.defaultProps = {
    data: {},
    onSave: () => {},
    onChange: () => {},
    onDelete: () => {},
    onCopy: () => {},
    onException: () => {},
    onEditInFuture: () => {}
}

AvailabilityForm.propTypes = {
    data: PropTypes.object,
    onSave: PropTypes.func,
    onDelete: PropTypes.func,
    onChange: PropTypes.func,
    onCopy: PropTypes.func,
    onException: PropTypes.func,
    onEditInFuture: PropTypes.func
}

export default AvailabilityForm
