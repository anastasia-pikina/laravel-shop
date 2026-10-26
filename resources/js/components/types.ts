export interface Product {
    id?: number,
    img?: string,
    title?: string,
    price?: number,
    available_quantity?: number,
    color?: string,
    type?: string
  }

export interface State {
    productInfo: Product,
    cartItems: Product[],
    items: Product[],
  }
