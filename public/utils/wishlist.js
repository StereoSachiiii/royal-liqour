import { fetchCartItems } from "./cart.js";

const WISHLIST_KEY = "wishlist";
const WISHLIST_EXPIRATION = 6 * 30 * 24 * 60 * 60 * 1000;

export const addToWishlist = async (id) => {
    try {
        const prevWishlist = await getWishlist();

        const numId = Number(id);

        if (prevWishlist.some(item => Number(item.id) === numId)) {
            console.log("Item already in wishlist");
            return prevWishlist;
        }

        const newItem = await fetchCartItems(numId);
        
        if (!newItem || !newItem.id) {
            console.error("Failed to fetch item or item has no id");
            return prevWishlist;
        }

        const newWishlist = [...prevWishlist, newItem];
        saveWishlist(newWishlist);
        console.log("Item added to wishlist:", newItem);
        return newWishlist;
    } catch (error) {
        console.error("Failed to add item to wishlist:", error);
        return await getWishlist();
    }
};

export const getWishlist = async () => {
    try {
        const stored = localStorage.getItem(WISHLIST_KEY);
        
        if (!stored) {
            return [];
        }

        const data = JSON.parse(stored);

        if (data.expiresAt && Date.now() > data.expiresAt) {
            localStorage.removeItem(WISHLIST_KEY);
            return [];
        }

        return data.items || [];
    } catch (error) {
        console.error("Failed to retrieve wishlist:", error);
        return [];
    }
};

export const saveWishlist = (newList) => {
    try {
        const data = {
            items: newList,
            expiresAt: Date.now() + WISHLIST_EXPIRATION
        };
        localStorage.setItem(WISHLIST_KEY, JSON.stringify(data));
        console.log("Wishlist saved:", newList);
    } catch (error) {
        console.error("Failed to save wishlist:", error);
    }
};
export const isInWishlist = (productId) => {
    const wishlist = JSON.parse(localStorage.getItem(WISHLIST_KEY));
    if (!wishlist || !wishlist.items) return false;
    
    return wishlist.items.some(item => Number(item.id) === Number(productId));
};