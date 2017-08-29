import BaseView from '../../../lib/baseview'
import $ from 'jquery'

const newIndex = (() => {
    let index = 0

    return () => {
        index += 1
        return `new_${index}`
    }
})()

const cloneEmptyLink = (link) => {
    const newLink = link.clone()
    newLink.find('input[name=target]').val('').attr('checked', false)

    return newLink
}

const cloneToNewLink = (link, index) => {
    const newLink = link.clone()
    const newIndexString = newIndex()
    console.log(newLink.find('.linkname'))

    newLink.find('.linkname').attr('name', `links[${index}][name]`)
    newLink.find('.linkurl').attr('name', `links[${index}][url]`)
    newLink.find('.linktarget').attr('name', `links[${index}][target]`)
    newLink.attr('data-index', newIndexString)
    newLink.find('[data-delete-button]').attr('data-index', newIndexString).find('input').attr('checked', true)
    return newLink
}

class View extends BaseView {
    constructor (element, options) {
        super(element)
        this.bindPublicMethods('handleDeleteClick',
                               'handleNewClick',
                               'bindClickEvents')
        this.$linkList = this.$.find('[data-link-list]').first()
        this.bindClickEvents()
        this.clonedLink = cloneEmptyLink(this.$linkList.find('[data-last]').first())
        console.log('DepartmentLinksView', this, options)
    }

    bindClickEvents() {
        this.$.find('[data-delete-button]').on('click', this.handleDeleteClick)
        this.$.find('[data-new-button]').on('click', this.handleNewClick)
    }

    unbindClickEvents() {
        this.$.find('[data-delete-button]').off('click', this.handleDeleteClick)
        this.$.find('[data-new-button]').off('click', this.handleNewClick)
    }

    resetLastLink() {
        this.$linkList.find('[data-link-entry]').attr('data-last', false).last().attr('data-last', true)
    }

    handleDeleteClick(ev) {
        ev.preventDefault()
        this.unbindClickEvents()
        const button = $(ev.target)
        const index = button.attr('data-index')
        const link = this.$.find(`[data-link-entry][data-index=${index}]`)
        link.remove()
        this.resetLastLink()
        this.bindClickEvents()
    }

    handleNewClick(ev) {
        ev.preventDefault()
        this.unbindClickEvents()
        const newLink = cloneToNewLink(this.clonedLink, this.$linkList.first().children().length)
        this.$linkList.append(newLink)
        this.resetLastLink()
        this.bindClickEvents()
    }
}

export default View;
