import { OfferLocation } from '../../store/offers/offers.state';

export interface Contact {
  name: string;
  email?: string;
  phone?: string;
}

export interface Offer {
  id: string;
  title: string;
  description: string;
  category: string;
  location: OfferLocation;
  currentDistance: number;
  status: 'draft' | 'published' | 'archived';
  createdAt: Date;
  updatedAt: Date;
  contact: Contact;
  imageUrl: string | null;
}
