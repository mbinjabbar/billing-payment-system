import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ProcedureCodesComponent } from './procedure-codes/procedure-codes.component';
import { InsuranceFirmsComponent } from './insurance-firms/insurance-firms.component';
import { AppConfigComponent } from './app-config/app-config.component';

type Tab = 'procedures' | 'insurance' | 'config';

@Component({
  selector: 'app-settings',
  standalone: true,
  imports: [CommonModule, ProcedureCodesComponent, InsuranceFirmsComponent, AppConfigComponent],
  templateUrl: './settings.component.html',
})
export class SettingsComponent {
  activeTab = signal<Tab>('procedures');

  setTab(tab: Tab) {
    this.activeTab.set(tab);
  }
}