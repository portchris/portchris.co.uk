/* tslint:disable:no-unused-variable */
import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { DebugElement } from '@angular/core';

import { ParticlesCompoonent } from './particles.component';

describe('ParticlesCompoonent', () => {
  let component: ParticlesCompoonent;
  let fixture: ComponentFixture<ParticlesCompoonent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ParticlesCompoonent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ParticlesCompoonent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
