/*
 * @author Stéphane LaFlèche <stephane.l@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

import * as React from "react";
import classNames from "classnames";
import { withRouter, RouteComponentProps } from "react-router-dom";
import SmartLink from "@library/routing/links/SmartLink";
import VanillaHeaderListItem from "@library/components/mebox/pieces/VanillaHeaderListItem";

export interface IHeaderNav {
    className?: string;
    to: string;
    children: React.ReactNode;
    linkClassName?: string;
    linkContentClassName?: string;
}

interface IProps extends IHeaderNav, RouteComponentProps<{}> {}

/**
 * Implements Navigation item component for header
 */
export class VanillaHeaderNavItem extends React.Component<IProps> {
    public render() {
        const isCurrent = this.currentPage();
        return (
            <VanillaHeaderListItem className={classNames(this.props.className, { isCurrent })}>
                <SmartLink to={this.props.to} className={classNames("vanillaHeaderNav-link", this.props.linkClassName)}>
                    <div
                        className={classNames("vanillaHeaderNav-linkContent", this.props.linkContentClassName, {
                            isCurrent,
                        })}
                    >
                        {this.props.children}
                    </div>
                </SmartLink>
            </VanillaHeaderListItem>
        );
    }

    /**
     * Checks if we're on the current page
     * Note that this won't work with non-canonical URLHeaderLogo.tsxs
     */
    public currentPage = (): boolean => {
        if (this.props.location && this.props.location.pathname) {
            return this.props.location.pathname === this.props.to;
        } else {
            return false;
        }
    };
}

export default withRouter<IProps>(VanillaHeaderNavItem);
