import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SharedDataService {
  private selectedItemSubject = new BehaviorSubject<any>(null);
  selectedItem$ = this.selectedItemSubject.asObservable();

  setSelectedItem(item: any): void {
    this.selectedItemSubject.next(item);
  }

  constructor() { }
}
