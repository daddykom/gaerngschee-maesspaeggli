import { Offer } from '../../shared/models/offer.model';

export interface OfferLocation {
  address: string;
  longitude: number;
  latitude: number;
}

export interface OfferJson {
  id: string;
  title: string;
  description: string;
  category: string;
  location: OfferLocation;
  status: 'draft' | 'published' | 'archived';
  createdAt: string;
  updatedAt: string;
  contact: { name: string; email?: string; phone?: string };
  imageUrl: string | null;
}
export interface OffersState {
  offers: Offer[];
  selectedOffer: Offer | null;
  loading: boolean;
  error: string | null;
  currentPosition: OfferLocation;
}

export const initialState: OffersState = {
  offers: [],
  selectedOffer: null,
  loading: false,
  error: null,
  currentPosition: {
    latitude: 47.556431,
    longitude: 7.591641,
    address: 'Münsterplatz, Basel',
  },
};
