/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import { Omit } from "@library/@types/utils";

export enum LoadStatus {
    PENDING = "PENDING",
    LOADING = "LOADING",
    SUCCESS = "SUCCESS",
    ERROR = "ERROR",
}

export interface ILoadable<T> {
    status: LoadStatus;
    error?: IApiError;
    data?: T;
}

export interface IApiResponse<DataType = any> {
    data: DataType;
    status: number;
    headers?: any;
}

export interface IFieldError {
    message: string; // translated message
    code: string; // translation code
    field: string;
    status?: number; // HTTP status
}

export interface IApiError {
    message: string;
    status: number;
    errors?: {
        [key: string]: IFieldError[];
    };
}

interface IMultiType<T> {
    recordType: T;
    recordID: number;
}

export type MultiTypeRecord<T, Subtract extends keyof T, TypeName extends string> = Omit<T, Subtract> &
    IMultiType<TypeName>;

export interface INavigationItem {
    name: string;
    url: string;
    parentID: number;
    recordID: number;
    sort: number | null;
    recordType: string;
}

export interface INavigationTreeItem extends INavigationItem {
    children: INavigationTreeItem[];
}
