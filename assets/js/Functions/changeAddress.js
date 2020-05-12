export default function checkAddress(value, form, parentForm) {
        let name = form.name
        let address = {...parentForm}
        address.children.streetName.value = value

        if (name === 'streetName' && address.children.streetNumber.value === '') {
            let x = value.split(' ')
            if (x.length > 1) {
                if (x[0].match(/^\d+$/) !== null) {
                    address.children.streetNumber.value = x[0]
                    delete x[0]
                    address.children.streetName.value = x.join(' ').trim()
                } else if (x.length > 2 && x[1].match(/^\d+$/) !== null && address.children.streetNumber.value === '') {
                    address.children.streetNumber.value = (x[0] + ' ' + x[1]).trim()
                    delete x[0]
                    delete x[1]
                    address.children.streetName.value = x.join(' ').trim()
                }
            }
        }

        return { ...address }

}