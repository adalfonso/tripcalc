class Queue {

    constructor() {
        this.items = [];
    }

    add(item) {
        if (this.contains(item)) {
            return {
                success: false, response: 'This item is already added.'
            };
        } else {
            this.items.push(item);
            return true;
        }
    }

    remove(item) {
        let index = this.items.indexOf(item);

        this.items.splice(index, 1);
    }

    contains(item) {
        return this.items.indexOf(item) != -1;
    }

    isEmpty() {
        // console.log(this.items.length === 0);
    }
}

export default Queue;