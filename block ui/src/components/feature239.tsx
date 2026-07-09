import { ArrowUpRight, ChevronRight, ChevronUp } from "lucide-react";
import React from "react";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface Image {
  src: string;
  alt: string;
  srcDark?: string;
}
interface Button {
  text: string;
  url: string;
  icon?: React.ReactNode;
}
interface Buttons {
  primary?: Button;
  secondary?: Button;
}
interface Badge {
  text: string;
  announcement?: string;
  url?: string;
}

interface FeatureSingleFocusProps {
  heading: string;
  description: string;
  image: Image;
  buttons?: Buttons;
  badge?: Badge;
  className?: string;
}

interface Feature239Props extends FeatureSingleFocusProps {}
type Props = Partial<Feature239Props>;

const defaultProps: FeatureSingleFocusProps = {
  heading: "Transform an Idea into Reality",
  description:
    "Unleash your creativity and break through barriers. Our platform brings all your ideas together in one intuitive workspace. Eliminate creative blocks and empower your team to imagine, design, and deliver.",
  label: "Section label",
  badge: {
    text: "Badge",
  },
  features: [
    {
      title: "Advanced Design Templates",
    },
    {
      title: "Collaborative Editing",
    },
    {
      title: "Resource Libraries",
    },
    {
      title: "Real-time Feedback Tools",
    },
    {
      title: "Custom Asset Export",
    },
    {
      title: "Version History",
    },
  ],
  stats: [
    {
      value: "400+",
      title: "Slack",
      description:
        "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quisquam, corporis!",
    },
    {
      value: "4000+",
      title: "Google Drive",
      description:
        "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quisquam, corporis!",
    },
    {
      value: "7000+",
      title: "Dropbox",
      description:
        "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quisquam, corporis!",
    },
    {
      value: "850+",
      title: "Github",
      description:
        "Lorem ipsum dolor sit, amet consectetur adipisicing elit. Quisquam, corporis!",
    },
  ],
  buttons: {
    secondary: {
      text: "Browse components",
      url: "https://www.shadcnblocks.com",
    },
  },
  image: {
    src: "https://deifkwefumgah.cloudfront.net/shadcnblocks/image-set/placeholder/images/1-1x1.jpg",
    alt: "Product workspace preview",
  },
};

const Feature239 = (props: Props) => {
  const { className, badge, heading, description, image, buttons } = {
    ...defaultProps,
    ...props,
  };

  const lines = heading.split("\n");

  return (
    <section className={cn("bg-background py-32", className)}>
      <div className="relative container flex flex-col items-center px-0! lg:pt-8">
        <DottedDiv>
          <div className="grid lg:grid-cols-2">
            <div className="flex w-full flex-col gap-8 px-10 py-20 md:px-14">
              {badge?.text && (
                <Badge
                  variant="outline"
                  className="flex w-fit cursor-pointer items-center gap-4 rounded-full px-6 py-2 transition-all ease-in-out hover:gap-6"
                >
                  <span className="text-sm font-medium tracking-tight text-muted-foreground">
                    {badge.text}
                  </span>
                  <ChevronRight className="size-4!" />
                </Badge>
              )}
              <h2 className="mb-5 text-5xl tracking-tight text-balance lg:text-5xl">
                {lines.map((line, i) => (
                  <React.Fragment key={i}>
                    {i > 0 && <br />}
                    {line}
                  </React.Fragment>
                ))}
              </h2>
              <p className="tracking-tight text-muted-foreground md:text-xl">
                {description}
              </p>
              {buttons?.secondary ? (
                <Button variant="outline" className="text-md h-12 w-fit rounded-full px-10" render={<a href={buttons.secondary.url ?? "#"} className="inline-flex items-center gap-2" />} nativeButton={false}>{buttons.secondary.text ?? "Get Started"}<ArrowUpRight className="size-4 shrink-0" /></Button>
              ) : null}
            </div>
            <DottedDiv className="group size-full place-self-end p-4 lg:w-4/6">
              <div className="relative h-full w-full bg-muted/50 p-4 transition-all ease-in-out group-hover:bg-muted">
                <div className="relative h-full w-full overflow-hidden rounded-3xl">
                  <img
                    src={image.src}
                    alt={image.alt}
                    className="aspect-square h-full w-full border border-muted object-cover object-top-left"
                  />
                  <div className="absolute inset-0 bg-linear-to-t from-black/70 to-transparent"></div>
                </div>

                <div className="absolute top-4 -ml-4 flex h-full w-full flex-col items-center justify-between p-10">
                  <p className="flex w-full items-center text-xl tracking-tighter text-background">
                    2025 <span className="mx-2 h-2.5 w-[1px] bg-background" />
                    March
                  </p>
                  <div className="flex flex-col items-center justify-center">
                    <h2 className="text-center text-6xl font-semibold tracking-tight text-background">
                      New <br />
                      Collection
                    </h2>
                    <div className="mt-2 h-1 w-6 rounded-full bg-background" />
                    <p className="mt-10 max-w-sm px-2 text-center text-lg leading-5 font-light tracking-tighter text-background/80">
                      Discover our latest release of beautifully crafted
                      components.
                    </p>
                  </div>
                  <a
                    href="#"
                    className="group mb-6 flex cursor-pointer flex-col items-center justify-center text-background"
                  >
                    <ChevronUp
                      size={30}
                      className="transition-all ease-in-out group-hover:-translate-y-2"
                    />
                    <p className="text-xl tracking-tight text-background">
                      See All
                    </p>
                  </a>
                </div>
              </div>
            </DottedDiv>
          </div>
        </DottedDiv>
      </div>
    </section>
  );
};

export { Feature239 };

const DottedDiv = ({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) => (
  <div className={cn("relative", className)}>
    <div className="absolute top-4 -left-25 h-[1.5px] w-[115%] bg-muted" />
    <div className="absolute bottom-4 -left-25 h-[1.5px] w-[115%] bg-muted" />
    <div className="absolute -top-25 left-4 h-[130%] w-[1.5px] bg-muted" />
    <div className="absolute -top-25 right-4 h-[130%] w-[1.5px] bg-muted" />
    <div className="absolute top-[12.5px] left-[12.5px] z-10 size-2 rounded-full bg-foreground" />
    <div className="absolute top-[12.5px] right-[12.5px] z-10 size-2 rounded-full bg-foreground" />
    <div className="absolute bottom-[12.5px] left-[12.5px] z-10 size-2 rounded-full bg-foreground" />
    <div className="absolute right-[12.5px] bottom-[12.5px] z-10 size-2 rounded-full bg-foreground" />
    {children}
  </div>
);
