export const sortByName = (a, b) => {
    return (a['name'] > b['name']) - (a['name'] < b['name'])
}