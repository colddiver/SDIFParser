# United States Swimming Interchange Format

## Introduction

United States Swimming has matured as an organization and expanded services to individuals and clubs.  To support this expansion, USS has developed a computer plan. One component is a standard interchange format for technical data.  Swimming data must be transmitted among the clubs, Local Swimming Committees (LSCs), and the USS headquarters office.  Exchanging meet results is one example, where data from a host club is distributed to swimmers and clubs using diskettes or modems.  Some LSCs are compiling swimmer statistics and would retype the data from printed sheets if electronic transmission were not available. A standard format promotes easy exchange of data and the development of new computer programs and services.  The goal is to preserve the valuable efforts of our volunteers.

To develop this standard, United States Swimming established an ad-hoc committee to review existing needs and prepare a draft design. A representative from US Masters Swimming and a coach familiar with high school and college swimming requirements contributed significantly to the final design.  The design is intended to allow all aquatic sports organizations to use the same standard.  New records can be added, and new codes or fields can be added to existing records.

The format incorporates a modular design.  Each file would combine the records into an order that corresponds to the type of data to be transmitted.  Meet entry records would have a specific order.  Time standards would have another order.  When specific record types are not needed, those records can be omitted.   Certain fields were declared to be "mandatory" for adequate identification of the data and to preserve unique identifiers.

## Format Design

SDI files have a SD3 extension. Example: meetrslt.sd3.

The basic design of the USS Interchange Format is a fixed record length for all records.  Data is grouped together by type of information, and records are linked to each other by common fields or by record order.  The records comprise a single type of information, e.g., one record for meet host data.  The file structure has an implied order of less frequent data preceding more frequent data, i.e., one meet, multiple teams, multiple athletes per team, etc.

Each record is one-hundred sixty-two (162) bytes in length,  with the last two bytes a carriage return and line feed. Byte 161 is an ASCII 13 and byte 162 is an ASCII 10.  Each record has a two byte record identifier.

## Coding conventions

The first byte of the first record MUST be the first byte of
the file, and MUST begin an A0 record.

Fields which are not used should be blank filled.

"No blanks" means that there may not be ANY blanks ANYWHERE
in the field;  "non-blank" means that there MAY be blanks in
the field, but there MUST be AT LEAST ONE non-blank character
as well.

There are two levels of mandatory fields. M1 - must be included for the record to be useful. M2 - an exceptions report must be generated for records containing blank M2 fields. M2 fields are necessary for the records to be processed by USS. M1 fields require a non-blank entry.

All undefined space is named "future use" and MUST be blank until defined explicitly.

The first two bytes of a record are always CONST, and are case sensitive.

All ALPHA fields are left justified* and can contain any letters, numbers and printable symbols.  Users may elect to have alphabetic data in upper and lower case or upper case only. *Alpha fields containing only numeric data should be right justified.

All INT data are stored as ASCII digits and should be right justified and blank filled.

All LOGICAL fields must contain a upper case 'T' an upper case 'F' or a blank.